<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense class="elevation-1">
                <v-icon class="mr-2" color="primary">mdi-clipboard-list-outline</v-icon>
                <v-toolbar-title class="font-weight-bold">Acompanhamento de Chamados</v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text class="pt-4">
                <!-- Filtros de Pesquisa -->
                <v-card outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                        <v-icon small left color="primary">mdi-filter</v-icon>
                        Filtros de Pesquisa
                    </v-card-title>
                    <v-card-text class="pt-3">
                        <v-row dense>
                            <v-col cols="12" md="3">
                                <v-text-field label="Nome do Paciente" autocomplete="off" hide-details outlined dense
                                    v-model="chamadoPesquisa.PACIENTE_NOME" @keyup.enter="pesquisar"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="2">
                                <v-text-field label="Nº do Chamado" type="number" hide-details outlined dense
                                    v-model="chamadoPesquisa.CHAMADO_ID" @keyup.enter="pesquisar"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-select label="Situação do Chamado" :items="situacoesChamado" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details outlined dense
                                    v-model="chamadoPesquisa.TG_SITUACAO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" md="2">
                                <v-text-field label="Data Abertura" type="date" hide-details outlined dense
                                    v-model="chamadoPesquisa.CHAMADO_DATA"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="2">
                                <v-select label="Prioridade" :items="prioridades" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details outlined dense
                                    v-model="chamadoPesquisa.TG_PRIORIDADE_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-row dense class="mt-2">
                            <v-col class="text-right">
                                <v-btn color="primary" tile @click="pesquisar">
                                    <v-icon left>mdi-magnify</v-icon>Pesquisar
                                </v-btn>
                                <v-btn color="red" dark tile @click="clear">
                                    <v-icon left>mdi-broom</v-icon>Limpar
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <!-- Tabela de Resultados -->
                <v-simple-table dense v-show="chamados.length" class="mb-0 border rounded">
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left font-weight-bold">Nº Chamado</th>
                                <th class="text-left font-weight-bold">Paciente</th>
                                <th class="text-left font-weight-bold">Data/Hora Abertura</th>
                                <th class="text-left font-weight-bold">Origem/Destino</th>
                                <th class="text-left font-weight-bold">Prioridade</th>
                                <th class="text-left font-weight-bold">Situação</th>
                                <th class="text-center font-weight-bold">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="chamado in chamados" :key="chamado.CHAMADO_ID">
                                <td class="font-weight-medium">{{ chamado.CHAMADO_ID }}</td>
                                <td>
                                    <div>{{ chamado.paciente ? chamado.paciente.PACIENTE_NOME : '-' }}</div>
                                    <small class="grey--text">{{ chamado.paciente && chamado.paciente.PACIENTE_CPF ? chamado.paciente.PACIENTE_CPF : 'Sem CPF' }}</small>
                                </td>
                                <td>{{ formatarDataHora(chamado.CHAMADO_DATA) }}</td>
                                <td>
                                    <div><v-icon x-small color="green">mdi-arrow-up</v-icon> {{ chamado.unidade_solicitante ? chamado.unidade_solicitante.UNIDADE_NOME : '-' }}</div>
                                    <div><v-icon x-small color="red">mdi-arrow-down</v-icon> {{ chamado.unidade_destino ? chamado.unidade_destino.UNIDADE_NOME : '-' }}</div>
                                </td>
                                <td>
                                    <v-chip x-small :color="getPrioridadeColor(chamado.TG_PRIORIDADE_ID)" dark>
                                        {{ descricaoTabelaGenerica(prioridades, chamado.TG_PRIORIDADE_ID) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip x-small :color="getSituacaoColor(chamado.TG_SITUACAO_ID)" dark class="font-weight-medium">
                                        {{ descricaoTabelaGenerica(situacoesChamado, chamado.TG_SITUACAO_ID) }}
                                    </v-chip>
                                </td>
                                <td class="text-center">
                                    <v-btn icon color="primary" @click="verDetalhes(chamado.CHAMADO_ID)" title="Visualizar Detalhes">
                                        <v-icon>mdi-eye</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table>

                <!-- Paginação -->
                <v-row class="mt-4" align="center" justify="center">
                    <v-col cols="12" sm="6" class="text-center">
                        <v-pagination v-show="pagination.total" v-model="pagination.current_page"
                            :length="pagination.last_page" total-visible="8" @input="onPageChange"></v-pagination>
                    </v-col>
                </v-row>

                <v-divider class="my-2"></v-divider>

                <v-row align="center" justify="center" class="text-center">
                    <v-col>
                        <v-chip outlined color="primary">
                            {{ pagination.total }} Chamado{{ pagination.total !== 1 ? 's' : '' }} Encontrado{{ pagination.total !== 1 ? 's' : '' }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Dialog de Detalhes (Read-Only) -->
        <v-dialog v-model="showDetailsModal" max-width="900" scrollable>
            <v-card v-if="showDetailsModal && chamadoSelecionado">
                <v-toolbar color="primary" dark flat dense>
                    <v-toolbar-title class="font-weight-bold">
                        Detalhes do Chamado Nº {{ chamadoSelecionado.CHAMADO_ID }}
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-chip color="white" class="primary--text font-weight-bold mr-2" small>
                        {{ descricaoTabelaGenerica(situacoesChamado, chamadoSelecionado.situacao_atual ? chamadoSelecionado.situacao_atual.TG_SITUACAO_ID : null) }}
                    </v-chip>
                    <v-btn icon @click="showDetailsModal = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>

                <v-card-text class="pt-4">
                    <!-- Informações do Paciente -->
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                            <v-icon small left color="primary">mdi-account</v-icon>
                            Paciente
                        </v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="4">
                                    <v-text-field label="Nome do Paciente" readonly filled dense hide-details
                                        :value="chamadoSelecionado.paciente ? chamadoSelecionado.paciente.PACIENTE_NOME : '-'"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="3">
                                    <v-text-field label="CPF" readonly filled dense hide-details
                                        :value="chamadoSelecionado.paciente && chamadoSelecionado.paciente.PACIENTE_CPF ? chamadoSelecionado.paciente.PACIENTE_CPF : 'Não Informado'"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="2">
                                    <v-text-field label="Idade" readonly filled dense hide-details
                                        :value="calcularIdade(chamadoSelecionado.paciente ? chamadoSelecionado.paciente.PACIENTE_DT_NASCIMENTO : null)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="3">
                                    <v-text-field label="Sexo" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(sexos, chamadoSelecionado.paciente ? chamadoSelecionado.paciente.TG_SEXO_ID : null)"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Dados do Chamado & Regulação -->
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                            <v-icon small left color="primary">mdi-clipboard-text</v-icon>
                            Dados do Atendimento e Regulação
                        </v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense class="mb-2">
                                <v-col cols="12" md="4">
                                    <v-text-field label="Tipo de Chamado" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(tiposChamado, chamadoSelecionado.TG_CHAMADO_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field label="Prioridade" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(prioridades, chamadoSelecionado.TG_PRIORIDADE_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="2">
                                    <v-text-field label="Data Abertura" readonly filled dense hide-details
                                        :value="formatarDataBR(chamadoSelecionado.CHAMADO_DATA)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="2">
                                    <v-text-field label="Horário Atendimento" readonly filled dense hide-details
                                        :value="chamadoSelecionado.CHAMADO_HORARIO_ATENDIMENTO || '-'"></v-text-field>
                                </v-col>
                            </v-row>

                            <v-divider class="my-2"></v-divider>

                            <v-row dense class="mb-2">
                                <v-col cols="12" md="6">
                                    <v-card outlined class="pa-2">
                                        <div class="caption font-weight-bold green--text mb-1">ORIGEM</div>
                                        <div class="body-2 font-weight-bold">{{ chamadoSelecionado.unidade_solicitante ? chamadoSelecionado.unidade_solicitante.UNIDADE_NOME : '-' }}</div>
                                        <div class="caption grey--text">
                                            Profissional: {{ chamadoSelecionado.CHAMADO_PROFISSIONAL_SOLICITANTE || '-' }} | 
                                            Setor: {{ chamadoSelecionado.CHAMADO_SETOR_SOLICITANTE || '-' }} | 
                                            Leito: {{ chamadoSelecionado.CHAMADO_LEITO_SOLICITANTE || '-' }}
                                        </div>
                                    </v-card>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-card outlined class="pa-2">
                                        <div class="caption font-weight-bold red--text mb-1">DESTINO</div>
                                        <div class="body-2 font-weight-bold">{{ chamadoSelecionado.unidade_destino ? chamadoSelecionado.unidade_destino.UNIDADE_NOME : '-' }}</div>
                                        <div class="caption grey--text">
                                            Setor: {{ chamadoSelecionado.CHAMADO_SETOR_DESTINO || '-' }} | 
                                            Leito: {{ chamadoSelecionado.CHAMADO_LEITO_DESTINO || '-' }}
                                        </div>
                                    </v-card>
                                </v-col>
                            </v-row>

                            <v-divider class="my-2"></v-divider>

                            <v-row dense>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Procedimento" readonly filled dense hide-details
                                        :value="chamadoSelecionado.procedimentos && chamadoSelecionado.procedimentos.length ? chamadoSelecionado.procedimentos[0].PROCEDIMENTO_DESCRICAO : '-'"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Diagnóstico" readonly filled dense hide-details
                                        :value="chamadoSelecionado.diagnosticos && chamadoSelecionado.diagnosticos.length ? chamadoSelecionado.diagnosticos[0].DIAGNOSTICO_DESCRICAO : '-'"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="9">
                                    <v-text-field label="Dispositivos" readonly filled dense hide-details
                                        :value="chamadoSelecionado.CHAMADO_DISPOSITIVOS || '-'"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="3">
                                    <v-text-field label="Peso (kg)" readonly filled dense hide-details
                                        :value="chamadoSelecionado.CHAMADO_PESO ? chamadoSelecionado.CHAMADO_PESO + ' kg' : '-'"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Sinais Vitais & Suporte -->
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                            <v-icon small left color="primary">mdi-pulse</v-icon>
                            Suporte e Sinais Vitais
                        </v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="4">
                                    <v-text-field label="Precaução" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(tiposPrecaucao, chamadoSelecionado.TG_TIPO_PRECAUCAO_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field label="Suporte Hemodinâmico" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(suportesHemodinamicos, chamadoSelecionado.TG_SUPORTE_HEMODINAMICO_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-text-field label="Suporte O2" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(suportesO2, chamadoSelecionado.TG_SUPORTE_O2_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="3">
                                    <v-text-field label="Temperatura" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(temperaturas, chamadoSelecionado.TG_TEMPERATURA_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="3">
                                    <v-text-field label="Pressão Arterial" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(pressoesArteriais, chamadoSelecionado.TG_PRESSAO_ARTERIAL_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="3">
                                    <v-text-field label="Frequência Cardíaca" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(frequenciasCardiacas, chamadoSelecionado.TG_FREQUENCIA_CARDIACA_ID)"></v-text-field>
                                </v-col>
                                <v-col cols="6" md="3">
                                    <v-text-field label="Saturação O2" readonly filled dense hide-details
                                        :value="descricaoTabelaGenerica(saturacoes, chamadoSelecionado.TG_SATURACAO_ID)"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <!-- Observações -->
                    <v-card outlined class="mb-4" v-if="chamadoSelecionado.CHAMADO_OBSERVACAO">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                            <v-icon small left color="primary">mdi-comment-text-outline</v-icon>
                            Observações
                        </v-card-title>
                        <v-card-text class="pt-3">
                            <div class="body-2 font-italic">{{ chamadoSelecionado.CHAMADO_OBSERVACAO }}</div>
                        </v-card-text>
                    </v-card>

                    <!-- Histórico de Situações -->
                    <v-card outlined>
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                            <v-icon small left color="primary">mdi-history</v-icon>
                            Histórico de Trâmite / Situação
                        </v-card-title>
                        <v-card-text class="pt-3">
                            <v-timeline dense align-top>
                                <v-timeline-item v-for="sit in chamadoSelecionado.situacoes" :key="sit.CHAMADO_SITUACAO_ID"
                                    :color="getSituacaoColor(sit.TG_SITUACAO_ID)" small>
                                    <v-row dense>
                                        <v-col cols="12" md="4">
                                            <strong>{{ descricaoTabelaGenerica(situacoesChamado, sit.TG_SITUACAO_ID) }}</strong>
                                            <div class="caption text-muted">{{ formatarDataHora(sit.CHAMADO_SITUACAO_DATA) }}</div>
                                        </v-col>
                                        <v-col cols="12" md="8">
                                            <div class="caption">
                                                Operador: {{ sit.usuario ? sit.usuario.USUARIO_NOME : 'Sistema' }}
                                            </div>
                                            <div class="body-2" v-if="sit.CHAMADO_SITUACAO_OBSERVACAO">
                                                <em>"{{ sit.CHAMADO_SITUACAO_OBSERVACAO }}"</em>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-timeline-item>
                            </v-timeline>
                        </v-card-text>
                    </v-card>
                </v-card-text>

                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" outlined tile @click="showDetailsModal = false">
                        Fechar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "ChamadoAcompanhamentoView",
    components: { TratarErroAjax },
    mixins: [UtilsMixins],

    props: {
        sexos: { type: Array, default: () => [] },
        prioridades: { type: Array, default: () => [] },
        situacoesChamado: { type: Array, default: () => [] },
        tiposChamado: { type: Array, default: () => [] },
        tiposPrecaucao: { type: Array, default: () => [] },
        suportesO2: { type: Array, default: () => [] },
        suportesHemodinamicos: { type: Array, default: () => [] },
        temperaturas: { type: Array, default: () => [] },
        frequenciasCardiacas: { type: Array, default: () => [] },
        pressoesArteriais: { type: Array, default: () => [] },
        saturacoes: { type: Array, default: () => [] }
    },

    data() {
        return {
            msgId: "msgChamadoAcompanhamentoView",
            msgIdDebug: "msgChamadoAcompanhamentoViewDebug",
            showDetailsModal: false
        };
    },

    mounted() {
        this.pesquisar();
    },

    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),

        chamados() {
            return this.$store.getters["ChamadoAcompanhamentoViewModule/getChamados"];
        },

        pagination: {
            get() {
                return this.$store.getters["ChamadoAcompanhamentoViewModule/getPagination"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoAcompanhamentoViewModule/setPagination", newValue);
            }
        },

        chamadoPesquisa: {
            get() {
                return this.$store.getters["ChamadoAcompanhamentoViewModule/getChamadoPesquisa"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoAcompanhamentoViewModule/setChamadoPesquisa", newValue);
            }
        },

        chamadoSelecionado() {
            return this.$store.getters["ChamadoAcompanhamentoViewModule/getChamadoSelecionado"];
        }
    },

    methods: {
        pesquisar() {
            this.pagination.current_page = 1;
            this.$store.dispatch("ChamadoAcompanhamentoViewModule/search", this.msgId);
        },

        clear() {
            this.$store.dispatch("ChamadoAcompanhamentoViewModule/setChamadoPesquisa", null);
            this.pesquisar();
        },

        onPageChange() {
            this.$store.dispatch("ChamadoAcompanhamentoViewModule/search", this.msgId);
        },

        verDetalhes(id) {
            this.$store.dispatch("ChamadoAcompanhamentoViewModule/buscarChamado", { id, msgId: this.msgId })
                .then(() => {
                    this.showDetailsModal = true;
                });
        },

        getSituacaoColor(situacaoId) {
            switch (Number(situacaoId)) {
                case 1: return "grey darken-1"; // Aberto
                case 2: return "blue darken-2"; // Em análise
                case 3: return "orange darken-3"; // Em atendimento
                case 4: return "green darken-2"; // Concluído
                case 5: return "red darken-2"; // Cancelado
                default: return "grey";
            }
        },

        getPrioridadeColor(prioridadeId) {
            switch (Number(prioridadeId)) {
                case 1: return "red darken-3"; // Alta / Emergência
                case 2: return "orange darken-2"; // Média / Urgência
                case 3: return "green darken-1"; // Baixa
                default: return "blue-grey";
            }
        },

        descricaoTabelaGenerica(lista, colunaId) {
            if (!lista || !colunaId) return "-";
            let item = lista.find(item => Number(item.COLUNA_ID) === Number(colunaId));
            return item ? item.DESCRICAO : "-";
        },

        calcularIdade(data) {
            if (!data) return "-";
            return moment().diff(moment(data), "years") + " anos";
        }
    }
}
</script>

<style scoped>
.border {
    border: 1px solid #e0e0e0;
}
</style>
