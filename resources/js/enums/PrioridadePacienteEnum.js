const PrioridadePacienteEnum = Object.freeze({
    VERMELHO: 1,
    LARANJA: 2,
    AMARELO: 3,
    VERDE: 4,
    AZUL: 5
});

const PrioridadePacienteCor = Object.freeze({
    [PrioridadePacienteEnum.VERMELHO]: "red darken-3",
    [PrioridadePacienteEnum.LARANJA]: "orange darken-2",
    [PrioridadePacienteEnum.AMARELO]: "amber darken-2",
    [PrioridadePacienteEnum.VERDE]: "green darken-1",
    [PrioridadePacienteEnum.AZUL]: "blue darken-2"
});

const getPrioridadeColor = prioridadeId => (
    PrioridadePacienteCor[Number(prioridadeId)] || "grey"
);

export { PrioridadePacienteCor, getPrioridadeColor };
export default PrioridadePacienteEnum;
