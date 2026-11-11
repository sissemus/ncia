const formatador = new Intl.DateTimeFormat('pt-BR', {
    timeZone: 'America/Fortaleza',
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23'
});

export function formatarDataHoraFortaleza(valor, segundos = false) {
    if (!valor) return '-';
    let texto = String(valor).trim().replace(' ', 'T');
    // DATETIME legado representa o horário local (UTC-3); ISO com offset mantém seu instante.
    if (/^\d{4}-\d{2}-\d{2}$/.test(texto)) texto += 'T00:00:00';
    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2}(\.\d+)?)?$/.test(texto)) texto += '-03:00';
    const data = valor instanceof Date ? valor : new Date(texto);
    if (Number.isNaN(data.getTime())) return String(valor);
    const partes = {};
    formatador.formatToParts(data).forEach(parte => { partes[parte.type] = parte.value; });
    return `${partes.day}/${partes.month}/${partes.year} ${partes.hour}:${partes.minute}${segundos ? `:${partes.second}` : ''}`;
}
