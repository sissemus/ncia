<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense class="elevation-1">
                <v-icon color="primary" class="mr-2">mdi-clipboard-check-outline</v-icon>
                <v-toolbar-title class="font-weight-bold">Filas de chamados</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon color="primary" @click="carregar">
                    <v-icon>mdi-refresh</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>

            <v-tabs v-model="abaAtiva" background-color="blue-grey lighten-5" color="primary">
                <v-tab v-for="aba in abasDisponiveis" :key="aba.id" :href="`#${aba.id}`">
                    <v-icon small left>{{ aba.icone }}</v-icon>
                    {{ aba.texto }}
                </v-tab>
            </v-tabs>

            <v-card-text>
                <v-alert v-if="abaAtiva === 'analise'" type="info" dense outlined>
                    Para continuar o fluxo, acesse <strong>Analisar Chamados</strong> no menu.
                </v-alert>
                <v-alert v-if="abaAtiva === 'atendimento'" type="info" dense outlined>
                    A conclusão ou o cancelamento do atendimento é realizado em
                    <strong>Acompanhamento de Chamados</strong>.
                </v-alert>

                <v-progress-linear v-if="carregandoFilaAtual" indeterminate color="primary"
                    class="mb-3"></v-progress-linear>

                <v-alert v-if="!carregandoFilaAtual && !chamadosAtuais.length" type="info" outlined>
                    Nenhum chamado {{ descricaoFilaVazia }}.
                </v-alert>

                <v-simple-table v-if="chamadosAtuais.length" dense>
                    <thead>
                        <tr>
                            <th>Nº Chamado</th>
                            <th>Paciente</th>
                            <th>Data/Hora</th>
                            <th>Procedimento</th>
                            <th>Prioridade</th>
                            <th v-if="abaAtiva === 'abertos' && podeAnalisar">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="chamado in chamadosAtuais" :key="chamado.CHAMADO_ID">
                            <td>{{ chamado.CHAMADO_ID }}</td>
                            <td>{{ nomePaciente(chamado) }}</td>
                            <td>{{ formatarDataHora(chamado.CHAMADO_DATA) }}</td>
                            <td>{{ procedimento(chamado) }}</td>
                            <td>
                                <v-chip x-small dark :color="corPrioridade(chamado.TG_PRIORIDADE_ID)">
                                    {{ descricao(prioridades, chamado.TG_PRIORIDADE_ID) }}
                                </v-chip>
                            </td>
                            <td class="text-center" v-if="abaAtiva === 'abertos' && podeAnalisar">
                                <v-btn icon color="primary" title="Analisar chamado"
                                    :loading="recepcionandoId === chamado.CHAMADO_ID"
                                    :disabled="recepcionandoId !== null"
                                    @click="recepcionar(chamado.CHAMADO_ID)">
                                    <v-icon>mdi-clipboard-search-outline</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </v-simple-table>

                <v-pagination v-if="paginacaoAtual.total" class="mt-4" v-model="paginaAtual"
                    :length="paginacaoAtual.last_page" @input="carregarAba"></v-pagination>
            </v-card-text>
        </v-card>

        <v-card v-if="podeCancelarPrazo" class="elevation-2 rounded-lg mt-4">
            <v-toolbar flat dense class="elevation-1">
                <v-icon color="orange darken-2" class="mr-2">mdi-clock-alert-outline</v-icon>
                <v-toolbar-title class="font-weight-bold">Chamados com prazo excedido</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon color="orange darken-2" @click="carregarExpirados">
                    <v-icon>mdi-refresh</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgExpiradosId"></tratar-erro-ajax>

            <v-card-text>
                <v-alert type="warning" dense outlined>
                    Chamados que permanecem abertos por 24 horas ou mais devem ser avaliados e cancelados
                    manualmente quando não houver continuidade do atendimento.
                </v-alert>

                <v-progress-linear v-if="carregandoExpirados" indeterminate color="orange darken-2"
                    class="mb-3"></v-progress-linear>

                <v-alert v-if="!carregandoExpirados && !chamadosExpirados.length" type="info" outlined>
                    Nenhum chamado aberto com prazo excedido.
                </v-alert>

                <v-simple-table v-if="chamadosExpirados.length" dense>
                    <thead>
                        <tr>
                            <th>Nº Chamado</th>
                            <th>Paciente</th>
                            <th>Data/Hora</th>
                            <th>Tempo em aberto</th>
                            <th>Situação</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="chamado in chamadosExpirados" :key="chamado.CHAMADO_ID">
                            <td>{{ chamado.CHAMADO_ID }}</td>
                            <td>{{ nomePaciente(chamado) }}</td>
                            <td>{{ formatarDataHora(chamado.CHAMADO_DATA) }}</td>
                            <td>{{ tempoEmAberto(chamado.CHAMADO_DATA) }}</td>
                            <td>
                                <v-chip x-small dark color="orange darken-2">
                                    ABERTO HÁ MAIS DE 24H
                                </v-chip>
                            </td>
                            <td class="text-center">
                                <v-btn icon color="error" title="Cancelar por prazo excedido"
                                    :loading="cancelandoId === chamado.CHAMADO_ID"
                                    :disabled="cancelandoId !== null"
                                    @click="cancelarExpirado(chamado)">
                                    <v-icon>mdi-cancel</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </v-simple-table>

                <v-pagination v-if="paginacaoExpirados.total" class="mt-4"
                    v-model="paginacaoExpirados.current_page" :length="paginacaoExpirados.last_page"
                    @input="carregarExpirados"></v-pagination>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";

export default {
    name: "Home",
    components: { TratarErroAjax },
    props: {
        usuario_logado: { type: Object },
        prioridades: { type: Array, default: () => [] },
        alerta_analise: { type: Object },
    },
    data() {
        return {
            msgId: "msgHomeChamados",
            msgExpiradosId: "msgHomeChamadosExpirados",
            abaAtiva: null,
            filas: { abertos: [], analise: [], atendimento: [] },
            paginacoes: {
                abertos: { current_page: 1, total: 0, last_page: 1 },
                analise: { current_page: 1, total: 0, last_page: 1 },
                atendimento: { current_page: 1, total: 0, last_page: 1 },
            },
            carregandoFilas: { abertos: false, analise: false, atendimento: false },
            chamadosExpirados: [],
            paginacaoExpirados: { current_page: 1, total: 0, last_page: 1 },
            carregandoExpirados: false,
            recepcionandoId: null,
            cancelandoId: null,
        };
    },
    computed: {
        ...mapGetters({ baseUrl: "getBaseUrl" }),
        perfilIds() {
            const perfis = this.usuario_logado && this.usuario_logado.usuarioPerfis
                ? this.usuario_logado.usuarioPerfis : [];
            return perfis
                .filter(item => Number(item.USUARIO_PERFIL_ATIVO) === 1)
                .map(item => Number(item.PERFIL_ID || (item.perfil && item.perfil.PERFIL_ID)));
        },
        podeAnalisar() {
            return [1, 2, 3].some(id => this.perfilIds.includes(id));
        },
        podeVisualizarAbertos() {
            return [1, 2, 3, 4].some(id => this.perfilIds.includes(id));
        },
        podeVisualizarAnalise() {
            return [1, 2, 3].some(id => this.perfilIds.includes(id));
        },
        podeVisualizarAtendimento() {
            return [1, 2, 3, 5].some(id => this.perfilIds.includes(id));
        },
        podeCancelarPrazo() {
            return [1, 2, 3, 5].some(id => this.perfilIds.includes(id));
        },
        abasDisponiveis() {
            const abas = [];
            if (this.podeVisualizarAbertos) {
                abas.push({ id: "abertos", texto: "Abertos", icone: "mdi-inbox-arrow-down" });
            }
            if (this.podeVisualizarAnalise) {
                abas.push({ id: "analise", texto: "Em análise", icone: "mdi-clipboard-search-outline" });
            }
            if (this.podeVisualizarAtendimento) {
                abas.push({ id: "atendimento", texto: "Em atendimento", icone: "mdi-ambulance" });
            }
            return abas;
        },
        chamadosAtuais() {
            return this.abaAtiva ? this.filas[this.abaAtiva] : [];
        },
        paginacaoAtual() {
            return this.abaAtiva ? this.paginacoes[this.abaAtiva]
                : { current_page: 1, total: 0, last_page: 1 };
        },
        paginaAtual: {
            get() {
                return this.paginacaoAtual.current_page;
            },
            set(pagina) {
                if (this.abaAtiva) {
                    this.paginacoes[this.abaAtiva].current_page = pagina;
                }
            },
        },
        carregandoFilaAtual() {
            return this.abaAtiva ? this.carregandoFilas[this.abaAtiva] : false;
        },
        descricaoFilaVazia() {
            if (this.abaAtiva === "analise") return "em análise";
            if (this.abaAtiva === "atendimento") return "em atendimento";
            return "aberto hoje";
        },
    },
    watch: {
        abaAtiva(novaAba, abaAnterior) {
            if (novaAba && novaAba !== abaAnterior) {
                this.carregarAba();
            }
        },
    },
    mounted() {
        this.$store.dispatch("AuthModule/setUsuario", this.usuario_logado);
        this.abaAtiva = this.abasDisponiveis.length ? this.abasDisponiveis[0].id : null;
        if (this.podeCancelarPrazo) {
            this.carregarExpirados();
        }
    },
    methods: {
        carregar() {
            this.carregarAba();
            if (this.podeCancelarPrazo) {
                this.carregarExpirados();
            }
        },
        carregarAba() {
            if (!this.abaAtiva) return Promise.resolve();
            const aba = this.abaAtiva;
            const paginacao = this.paginacoes[aba];
            const url = aba === "abertos"
                ? `${this.baseUrl}/home/chamados-abertos`
                : `${this.baseUrl}/home/chamados-operacionais`;
            const params = { page: paginacao.current_page };
            if (aba !== "abertos") {
                params.situacao = aba === "analise" ? 2 : 3;
            }
            this.carregandoFilas[aba] = true;
            return axios.get(url, { params }).then(response => {
                this.filas[aba] = response.data.data;
                this.paginacoes[aba] = this.criarPaginacao(response.data);
            }).catch(error => this.erro(error, this.msgId)).finally(() => {
                this.carregandoFilas[aba] = false;
            });
        },
        carregarExpirados() {
            this.carregandoExpirados = true;
            return axios.get(`${this.baseUrl}/home/chamados-expirados`, {
                params: { page: this.paginacaoExpirados.current_page },
            }).then(response => {
                this.chamadosExpirados = response.data.data;
                this.paginacaoExpirados = this.criarPaginacao(response.data);
            }).catch(error => this.erro(error, this.msgExpiradosId)).finally(() => {
                this.carregandoExpirados = false;
            });
        },
        recepcionar(id) {
            if (this.recepcionandoId !== null) return;
            this.recepcionandoId = id;
            axios.post(`${this.baseUrl}/chamado_analisar/recepcionar`, { CHAMADO_ID: id })
                .then(() => {
                    window.location.href = `${this.baseUrl}/chamado_analisar?chamado=${id}`;
                })
                .catch(error => this.erro(error, this.msgId))
                .finally(() => {
                    this.recepcionandoId = null;
                });
        },
        cancelarExpirado(chamado) {
            if (this.cancelandoId !== null) return;
            Swal.fire({
                title: "Cancelar chamado por prazo excedido?",
                text: `O chamado Nº ${chamado.CHAMADO_ID} será cancelado com o motivo “Prazo de 24 horas excedido”.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sim, cancelar",
                cancelButtonText: "Não",
                confirmButtonColor: "#D32F2F",
                cancelButtonColor: "#757575",
            }).then(resultado => {
                if (!resultado.isConfirmed) return;
                this.cancelandoId = chamado.CHAMADO_ID;
                axios.post(`${this.baseUrl}/home/chamados-expirados/cancelar`, {
                    CHAMADO_ID: chamado.CHAMADO_ID,
                }).then(response => this.carregarExpirados().then(() => {
                    Swal.fire("Sucesso", response.data.msg, "success");
                })).catch(error => this.erro(error, this.msgExpiradosId)).finally(() => {
                    this.cancelandoId = null;
                });
            });
        },
        criarPaginacao(dados) {
            return {
                current_page: dados.current_page,
                total: dados.total,
                last_page: dados.last_page,
            };
        },
        nomePaciente(chamado) {
            return chamado.paciente ? chamado.paciente.PACIENTE_NOME : "-";
        },
        procedimento(chamado) {
            return chamado.procedimentos && chamado.procedimentos.length
                ? chamado.procedimentos[0].PROCEDIMENTO_DESCRICAO : "-";
        },
        tempoEmAberto(data) {
            if (!data) return "-";
            const dataAbertura = this.converterData(data);
            if (!dataAbertura) return "-";
            const horas = Math.max(0, Math.floor((Date.now() - dataAbertura.getTime()) / 3600000));
            const dias = Math.floor(horas / 24);
            const horasRestantes = horas % 24;
            return dias ? `${dias}d ${horasRestantes}h` : `${horas}h`;
        },
        descricao(lista, id) {
            const item = (lista || []).find(x => Number(x.COLUNA_ID) === Number(id));
            return item ? item.DESCRICAO : "-";
        },
        corPrioridade(id) {
            const descricao = this.descricao(this.prioridades, id).toUpperCase();
            if (descricao.indexOf("VERMELHO") >= 0) return "red";
            if (descricao.indexOf("LARANJA") >= 0) return "orange";
            if (descricao.indexOf("AMARELO") >= 0) return "yellow darken-2";
            return "green";
        },
        formatarDataHora(data) {
            const dataConvertida = this.converterData(data);
            return dataConvertida ? dataConvertida.toLocaleString("pt-BR") : "-";
        },
        converterData(data) {
            if (!data) return null;
            const valor = String(data)
                .replace(" ", "T")
                .replace(/(\.\d{3})\d+$/, "$1");
            const dataConvertida = new Date(valor);
            return Number.isNaN(dataConvertida.getTime()) ? null : dataConvertida;
        },
        erro(error, id) {
            this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                id,
                response: error.response,
            }, { root: true });
        },
    },
};
</script>

<style scoped></style>
