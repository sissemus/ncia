import moment from "moment";

const formatador = new Intl.DateTimeFormat('pt-BR', {
    timeZone: 'America/Fortaleza',
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23'
});

export default {
    data() {
        return {
            TipoDocumentoEnum: {
                CPF: 2,
            },
            chartColors: {
                red: 'rgb(255, 99, 132)',
                orange: 'rgb(255, 159, 64)',
                yellow: 'rgb(255, 205, 86)',
                green: 'rgb(75, 192, 192)',
                blue: 'rgb(54, 162, 235)',
                purple: 'rgb(153, 102, 255)',
                grey: 'rgb(201, 203, 207)',
                deepOrange: 'rgb(255, 87, 34)',
                lightGreen: 'rgb(139,195,74)'
            },
            EventoEscalaEnum: {
                CADASTRADA: 1,
                ATUALIZADA: 2,
                AVALIADA: 3,
                DEFERIDA: 4,
                INDEFERIDA: 5,
                CLONADA: 6,
            },
        }
    },

    methods: {
        formatarDataHoraFortaleza(valor, segundos = false) {
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
        },
        formatarIdade(dataNascimento) {
            if (!dataNascimento) return '-';

            const nascimento = moment(dataNascimento).startOf('day');
            const hoje = moment().startOf('day');
            if (!nascimento.isValid() || nascimento.isAfter(hoje)) return '-';

            const anos = hoje.diff(nascimento, 'years');
            if (anos >= 1) return `${anos} ${anos === 1 ? 'ano' : 'anos'}`;

            const meses = hoje.diff(nascimento, 'months');
            return `${meses} ${meses === 1 ? 'mês' : 'meses'}`;
        },
        formatarDataSQL(value, formato = 'YYYY-MM-DD') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value).format(formato);
            }
        },
        formatarDataHora(value, formato = 'DD/MM/YYYY HH:mm') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value).format(formato);
            }
        },
        formatarDataBR(value, formato = 'DD/MM/YYYY') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value, 'YYYY-MM-DD').format(formato);
            }
        },
        incrementarData(value, incremento, tipo = 'days') {
            return moment(value, 'YYYY-MM-DD').add(incremento, tipo).format('YYYY-MM-DD')
        },
        calcularIdade(data) {
            return moment().diff(data,'years', false);
        },
        calcularTempoEspera(data) {
            return moment().diff(data,'days', false);
        },
        transparentize: function(color, opacity) {
            let alpha = opacity === undefined ? 0.2 : 1 - opacity;
            return Color(color).alpha(alpha).rgbString();
        },
        formatarMoedaBR(value){
            return 'R$ '+ parseFloat(value).toLocaleString('pt-br',{minimumFractionDigits:2});
        },

        parseData(valor,formatoEntrda="DD/MM/YYYY"){
            if(valor)
                return moment(valor,formatoEntrda);
            else return '';
        },
        formatarData(value, formato = 'DD/MM/YYYY') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value).format(formato);
            }
        },
        formatarPercentual(value){
            return parseFloat(value).toLocaleString() + " %";
        }
    },

}
