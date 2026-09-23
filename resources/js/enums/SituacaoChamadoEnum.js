const SituacaoChamadoEnum = Object.freeze({
    ABERTO: 1,
    EM_ANALISE: 2,
    EM_ATENDIMENTO: 3,
    CONCLUIDO: 4,
    CANCELADO: 5
});

const SituacaoChamadoCor = Object.freeze({
    [SituacaoChamadoEnum.ABERTO]: "grey darken-1",
    [SituacaoChamadoEnum.EM_ANALISE]: "blue darken-2",
    [SituacaoChamadoEnum.EM_ATENDIMENTO]: "orange darken-3",
    [SituacaoChamadoEnum.CONCLUIDO]: "green darken-2",
    [SituacaoChamadoEnum.CANCELADO]: "red darken-2"
});

const SituacaoChamadoCorTexto = Object.freeze({
    [SituacaoChamadoEnum.ABERTO]: "grey--text text--darken-3",
    [SituacaoChamadoEnum.EM_ANALISE]: "blue--text text--darken-3",
    [SituacaoChamadoEnum.EM_ATENDIMENTO]: "orange--text text--darken-4",
    [SituacaoChamadoEnum.CONCLUIDO]: "green--text text--darken-3",
    [SituacaoChamadoEnum.CANCELADO]: "red--text text--darken-3"
});

const SituacaoChamadoTipoAlerta = Object.freeze({
    [SituacaoChamadoEnum.CONCLUIDO]: "success",
    [SituacaoChamadoEnum.CANCELADO]: "error"
});

const getSituacaoChamadoColor = situacaoId => (
    SituacaoChamadoCor[Number(situacaoId)] || "grey"
);

const getSituacaoChamadoTextColor = situacaoId => (
    SituacaoChamadoCorTexto[Number(situacaoId)] || "grey--text"
);

const getSituacaoChamadoAlertType = situacaoId => (
    SituacaoChamadoTipoAlerta[Number(situacaoId)] || "info"
);

export {
    SituacaoChamadoCor,
    SituacaoChamadoCorTexto,
    SituacaoChamadoTipoAlerta,
    getSituacaoChamadoColor,
    getSituacaoChamadoTextColor,
    getSituacaoChamadoAlertType
};
export default SituacaoChamadoEnum;
