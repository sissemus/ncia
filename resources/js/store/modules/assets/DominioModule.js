export default {
    namespaced: true,
    state: {
        sexos: [],
        escolaridades: [],
        tipo_dependentes: [],
        tipo_finalizacao_dependentes: [],
        tipo_documento_pensoes: [],
        tipo_finalizacao_pensionistas: [],
        estadosCivis: [],
        tiposSanguineos: [],
        rhsMais: [],
        tiposDocumentos: [],
        ufs: [],
        bancos: [],
        tiposContasBancarias: [],
        tiposPix: [],
        tipoUnidades:[],
        unidadePortes:[],
        tiposFinalizacaoPensionista: [],
        tiposEntradaFuncionario: [],
        tiposSaidaFuncionario: [],
        setores: [],
        vinculos: [],
        finsLotacao: [],
        atribuicoes: [],
        cargasHorariasAtribuicao: [],
        tipoAfastamentos:[],
        tiposContatos: [],
        lotacaoTiposFim: [],
        tipoStatusEscala: [],
        tipoFeriados: []
    },
    getters: {
        getLotacaoTiposFim(state) {
            return state.lotacaoTiposFim
        },
        getTiposContatos(state) {
            return state.tiposContatos
        },
        getCargasHorariasAtribuicao(state) {
            return state.cargasHorariasAtribuicao
        },
        getAtribuicoes(state) {
            return state.atribuicoes
        },
        getFinsLotacao(state) {
            return state.finsLotacao
        },
        getVinculos(state) {
            return state.vinculos
        },
        getSetores(state) {
            return state.setores
        },
        getTiposSaidaFuncionario(state) {
            return state.tiposSaidaFuncionario
        },
        getTiposEntradaFuncionario(state) {
            return state.tiposEntradaFuncionario
        },
        getTiposFinalizacaoPensionista(state) {
            return state.tiposFinalizacaoPensionista
        },
        getTiposPix(state) {
            return state.tiposPix
        },
        getTiposContasBancarias(state) {
            return state.tiposContasBancarias
        },
        getBancos(state) {
            return state.bancos
        },
        getUfs(state) {
            return state.ufs
        },
        getTiposDocumentos(state) {
            return state.tiposDocumentos
        },
        getRhsMais(state) {
            return state.rhsMais
        },
        getTiposSanguineos(state) {
            return state.tiposSanguineos
        },
        getEstadosCivis(state) {
            return state.estadosCivis
        },
        getEscolaridades(state) {
            return state.escolaridades
        },
        getSexos(state) {
            return state.sexos
        },
        getDependentes(state) {
            return state.tipo_dependentes
        },
        getFinalizacaoDependentes(state) {
            return state.tipo_finalizacao_dependentes
        },
        getTipoDocumentoPensoes(state) {
            return state.tipo_documento_pensoes
        },
        getFinalizacaoPensionistas(state) {
            return state.tipo_finalizacao_pensionistas
        },
        getTipoUnidades(state) {
            return state.tipoUnidades;
        },
        getUnidadePortes(state) {
            return state.unidadePortes;
        },
        getTipoAfastamentos(state) {
            return state.tipoAfastamentos;
        },
        getTipoStatusEscala(state) {
            return state.tipoStatusEscala;
        },
        getTipoFeriados(state) {
            return state.tipoFeriados;
        }
    },
    mutations: {
        setLotacaoTiposFim(state, lotacaoTiposFim) {
            state.lotacaoTiposFim = JSON.parse(JSON.stringify(lotacaoTiposFim))
        },
        setTiposContatos(state, tiposContatos) {
            state.tiposContatos = JSON.parse(JSON.stringify(tiposContatos))
        },
        setCargasHorariasAtribuicao(state, cargasHorariasAtribuicao) {
            state.cargasHorariasAtribuicao = cargasHorariasAtribuicao
        },
        setAtribuicoes(state, atribuicoes) {
            state.atribuicoes = JSON.parse(JSON.stringify(atribuicoes))
        },
        setFinsLotacao(state, finsLotacao) {
            state.finsLotacao = JSON.parse(JSON.stringify(finsLotacao))
        },
        setVinculos(state, vinculos) {
            state.vinculos = JSON.parse(JSON.stringify(vinculos))
        },
        setSetores(state, setores) {
            state.setores = JSON.parse(JSON.stringify(setores))
        },
        setTiposSaidaFuncionario(state, tiposSaidaFuncionario) {
            state.tiposSaidaFuncionario = JSON.parse(JSON.stringify(tiposSaidaFuncionario))
        },
        setTiposEntradaFuncionario(state, tiposEntradaFuncionario) {
            state.tiposEntradaFuncionario = JSON.parse(JSON.stringify(tiposEntradaFuncionario))
        },
        setTiposFinalizacaoPensionista(state, tiposFinalizacaoPensionista) {
            state.tiposFinalizacaoPensionista = JSON.parse(JSON.stringify(tiposFinalizacaoPensionista))
        },
        setTiposPix(state, tiposPix) {
            state.tiposPix = tiposPix
        },
        setTiposContasBancarias(state, tiposContasBancarias) {
            state.tiposContasBancarias = JSON.parse(JSON.stringify(tiposContasBancarias))
        },
        setBancos(state, bancos) {
            state.bancos = JSON.parse(JSON.stringify(bancos))
        },
        setUfs(state, ufs) {
            state.ufs = JSON.parse(JSON.stringify(ufs))
        },
        setTiposDocumentos(state, tiposDocumentos) {
            state.tiposDocumentos = JSON.parse(JSON.stringify(tiposDocumentos))
        },
        setRhsMais(state, rhsMais) {
            state.rhsMais = rhsMais
        },
        setTiposSanguineos(state, tiposSanguineos) {
            state.tiposSanguineos = JSON.parse(JSON.stringify(tiposSanguineos))
        },
        setEstadosCivis(state, estadosCivis) {
            state.estadosCivis = JSON.parse(JSON.stringify(estadosCivis))
        },
        setEscolaridades(state, escolaridades) {
            state.escolaridades = JSON.parse(JSON.stringify(escolaridades))
        },
        setSexos(state, sexos) {
            state.sexos = JSON.parse(JSON.stringify(sexos))
        },
        setDependentes(state,tipo_dependentes) {
            state.tipo_dependentes = JSON.parse(JSON.stringify(tipo_dependentes))
        },
        setFinalizacaoDependentes(state,tipo_finalizacao_dependentes) {
            state.tipo_finalizacao_dependentes = JSON.parse(JSON.stringify(tipo_finalizacao_dependentes))
        },
        setTipoDocumentoPensoes(state,tipo_documento_pensoes) {
            state.tipo_documento_pensoes = JSON.parse(JSON.stringify(tipo_documento_pensoes))
        },
        setFinalizacaoPensionistas(state,tipo_finalizacao_pensionistas) {
            state.tipo_finalizacao_pensionistas = JSON.parse(JSON.stringify(tipo_finalizacao_pensionistas))
        },
        setTipoUnidades(state,tipoUnidades) {
            state.tipoUnidades = tipoUnidades;
        },
        setUnidadePortes(state,unidadePortes) {
            state.unidadePortes = unidadePortes;
        },
        setTipoAfastamentos(state,tipoAfastamentos) {
            state.tipoAfastamentos = tipoAfastamentos;
        },
        setTipoStatusEscala(state,tipoStatusEscala) {
            state.tipoStatusEscala = tipoStatusEscala;
        },
        setTipoFeriados(state,tipoFeriados) {
            state.tipoFeriados = tipoFeriados;
        }
    },
    actions: {
        setLotacaoTiposFim({commit}, lotacaoTiposFim) {
            commit('setLotacaoTiposFim', lotacaoTiposFim)
        },
        setTiposContatos({commit}, tiposContatos) {
            commit('setTiposContatos', tiposContatos)
        },
        setCargasHorariasAtribuicao({commit}, cargasHorariasAtribuicao) {
            commit('setCargasHorariasAtribuicao', cargasHorariasAtribuicao)
        },
        setAtribuicoes({commit}, atribuicoes) {
            commit('setAtribuicoes', atribuicoes)
        },
        setFinsLotacao({commit}, finsLotacao) {
            commit('setFinsLotacao', finsLotacao)
        },
        setVinculos({commit}, vinculos) {
            commit('setVinculos', vinculos)
        },
        setSetores({commit}, setores) {
            commit('setSetores', setores)
        },
        setTiposSaidaFuncionario({commit}, tiposSaidaFuncionario) {
            commit('setTiposSaidaFuncionario', tiposSaidaFuncionario)
        },
        setTiposEntradaFuncionario({commit}, tiposEntradaFuncionario) {
            commit('setTiposEntradaFuncionario', tiposEntradaFuncionario)
        },
        setTiposFinalizacaoPensionista({commit}, tiposFinalizacaoPensionista) {
            commit('setTiposFinalizacaoPensionista', tiposFinalizacaoPensionista)
        },
        setTiposPix({commit}, tiposPix) {
            commit('setTiposPix', tiposPix)
        },
        setTiposContasBancarias({commit}, tiposContasBancarias) {
            commit('setTiposContasBancarias', tiposContasBancarias)
        },
        setBancos({commit}, bancos) {
            commit('setBancos', bancos)
        },
        setUfs({commit}, ufs) {
            commit('setUfs', ufs)
        },
        setTiposDocumentos({commit}, tiposDocumentos) {
            commit('setTiposDocumentos', tiposDocumentos)
        },
        setRhsMais({commit}, rhsMais) {
            commit('setRhsMais', rhsMais)
        },
        setTiposSanguineos({commit}, tiposSanguineos) {
            commit('setTiposSanguineos', tiposSanguineos)
        },
        setEstadosCivis({commit}, estadosCivis) {
            commit('setEstadosCivis', estadosCivis)
        },
        setEscolaridades({commit}, escolaridades) {
            commit('setEscolaridades', escolaridades)
        },
        setSexos({commit}, sexos) {
            commit('setSexos', sexos)
        },
        setDependentes({commit},tipo_dependentes){
            commit('setDependentes',tipo_dependentes);
        },
        setFinalizacaoDependentes({commit},tipo_finalizacao_dependentes){
            commit('setFinalizacaoDependentes',tipo_finalizacao_dependentes);
        },
        setTipoDocumentoPensoes({commit},tipo_documento_pensoes){
            commit('setTipoDocumentoPensoes',tipo_documento_pensoes);
        },
        setFinalizacaoPensionistas({commit},tipo_finalizacao_pensionistas){
            commit('setFinalizacaoPensionistas',tipo_finalizacao_pensionistas);
        },
        setTipoUnidades({commit},tipoUnidades) {
            commit('setTipoUnidades',tipoUnidades);
        },
        setUnidadePortes({commit},unidadePortes) {
            commit('setUnidadePortes',unidadePortes);
        },
        setTipoAfastamentos({commit},tipoAfastamentos) {
            commit('setTipoAfastamentos',tipoAfastamentos);
        },
        setTipoStatusEscala({commit},tipoStatusEscala) {
            commit('setTipoStatusEscala',tipoStatusEscala);
        },
        setTipoFeriados({commit},tipoFeriados) {
            commit('setTipoFeriados',tipoFeriados);
        }
    }
}
