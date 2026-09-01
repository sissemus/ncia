<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense class="elevation-1">
                <v-icon class="mr-2" color="primary">mdi-ambulance</v-icon>
                <v-toolbar-title class="font-weight-bold">Abertura de Chamado</v-toolbar-title>
                <v-spacer></v-spacer>

                <!-- <v-btn color="blue-grey darken-1" outlined small class="mr-2" @click="preencherTesteComCpf">
                    <v-icon left small>mdi-test-tube</v-icon> Teste CPF
                </v-btn>

                <v-btn color="blue-grey darken-1" outlined small class="mr-2" @click="preencherTesteVulnerabilidade">
                    <v-icon left small>mdi-test-tube</v-icon> Teste Vuln.
                </v-btn>

                <v-btn color="red" outlined small @click="limpar">
                    <v-icon left small>mdi-broom</v-icon> Limpar
                </v-btn> -->
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text class="pt-4">
                <!-- ================= SEÇÃO 1: PACIENTE ================= -->
                <v-card outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                        <v-icon small left color="primary">mdi-account</v-icon>
                        1. Identificação do Paciente
                    </v-card-title>

                    <v-card-text class="pt-3">
                        <v-row dense align="center">
                            <v-col cols="12" class="pb-1">
                                <v-checkbox label="Paciente em vulnerabilidade social" dense hide-details
                                    v-model="pacienteVulnerabilidadeSocial" @change="alterarTipoPaciente"></v-checkbox>
                            </v-col>
                        </v-row>

                        <!-- Paciente com CPF -->
                        <template v-if="!pacienteVulnerabilidadeSocial">
                            <v-row dense align="center" class="mt-1">
                                <v-col cols="12" sm="5" md="4">
                                    <v-text-field label="CPF*" v-mask="'###.###.###-##'" autocomplete="off" outlined
                                        dense hide-details v-model="cpfPesquisa" @keyup.enter="buscarPaciente">
                                        <template v-slot:append>
                                            <v-btn icon small @click="buscarPaciente" :loading="consultandoPaciente">
                                                <v-icon>mdi-magnify</v-icon>
                                            </v-btn>
                                        </template>
                                    </v-text-field>
                                </v-col>

                                <v-col cols="12" sm="3" md="2">
                                    <v-btn block color="primary" dense height="40" tile :loading="consultandoPaciente"
                                        @click="buscarPaciente">
                                        Consultar
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" sm="4" md="6" v-if="paciente">
                                    <v-alert type="success" text dense class="ma-0 py-2">
                                        Paciente localizado com sucesso.
                                    </v-alert>
                                </v-col>
                            </v-row>

                            <v-row dense v-if="paciente" class="mt-2 grey lighten-5 pa-2 rounded">
                                <v-col cols="12" md="4">
                                    <v-text-field label="Nome Completo" readonly dense hide-details filled
                                        :value="paciente.PACIENTE_NOME"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="2">
                                    <v-text-field label="CPF" readonly dense hide-details filled
                                        :value="paciente.PACIENTE_CPF"></v-text-field>
                                </v-col>
                                <v-col cols="6" sm="3" md="2">
                                    <v-text-field label="Nascimento" readonly dense hide-details filled
                                        :value="formatarDataBR(paciente.PACIENTE_DT_NASCIMENTO)"></v-text-field>
                                </v-col>
                                <v-col cols="6" sm="3" md="2">
                                    <v-text-field label="Idade" readonly dense hide-details filled
                                        :value="calcularIdade(paciente.PACIENTE_DT_NASCIMENTO)"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="2">
                                    <v-text-field label="Sexo" readonly dense hide-details filled
                                        :value="descricaoTabelaGenerica(sexos, paciente.TG_SEXO_ID)"></v-text-field>
                                </v-col>
                            </v-row>
                        </template>

                        <!-- Paciente Vulnerabilidade -->
                        <template v-else>
                            <v-alert type="info" text dense class="my-2">
                                Paciente temporário vinculado exclusivamente a este chamado.
                            </v-alert>

                            <v-row dense>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Nome Completo (opcional)" outlined dense hide-details
                                        autocomplete="off" v-model="pacienteTemporario.PACIENTE_NOME"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="3">
                                    <v-text-field label="Data de Nascimento (opcional)" type="date" outlined dense
                                        hide-details autocomplete="off"
                                        v-model="pacienteTemporario.PACIENTE_DT_NASCIMENTO"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="3">
                                    <v-select label="Sexo*" :items="sexos" item-value="COLUNA_ID" item-text="DESCRICAO"
                                        outlined dense hide-details v-model="pacienteTemporario.TG_SEXO_ID"></v-select>
                                </v-col>
                            </v-row>
                        </template>
                    </v-card-text>
                </v-card>

                <!-- ================= SEÇÃO 2: DADOS DO CHAMADO ================= -->
                <v-card outlined :disabled="!formularioChamadoDisponivel" class="mb-2">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                        <v-icon small left color="primary">mdi-file-document-edit</v-icon>
                        2. Dados do Atendimento e Regulação
                    </v-card-title>

                    <v-card-text class="pt-3">
                        <!-- 2.1 Informações Gerais do Chamado -->
                        <div class="caption font-weight-bold grey--text text--darken-2 mb-2">GERAL & PRIORIDADE</div>
                        <v-row dense class="mb-2">
                            <v-col cols="6" sm="4" md="2">
                                <v-text-field label="Data Solicitação" readonly filled dense hide-details
                                    :value="dataSolicitacao"></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="4" md="2">
                                <v-text-field label="Hora" readonly filled dense hide-details
                                    :value="horaSolicitacao"></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="4" md="3">
                                <v-select label="Tipo de Chamado*" :items="tiposChamado" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" outlined dense hide-details
                                    v-model="chamado.TG_CHAMADO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="3">
                                <v-select label="Prioridade*" :items="prioridades" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" outlined dense hide-details
                                    v-model="chamado.TG_PRIORIDADE_ID"></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="2">
                                <v-text-field label="Horário Atendimento" type="time" outlined dense hide-details
                                    v-model="chamado.CHAMADO_HORARIO_ATENDIMENTO"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12" class="pt-1">
                                <v-radio-group v-model="chamado.CHAMADO_AMBULANCIA_EXTRA" row dense hide-details
                                    class="mt-1">
                                    <template v-slot:label>
                                        <span class="body-2 font-weight-medium mr-2">Necessita de Ambulância
                                            Extra?*</span>
                                    </template>
                                    <v-radio label="Sim" :value="1"></v-radio>
                                    <v-radio label="Não" :value="0"></v-radio>
                                </v-radio-group>
                            </v-col>
                        </v-row>

                        <v-divider class="my-3"></v-divider>

                        <!-- 2.2 Rota: Origem vs Destino -->
                        <div class="caption font-weight-bold grey--text text--darken-2 mb-2">ROTA (ORIGEM E DESTINO)
                        </div>
                        <v-row dense class="mb-2">
                            <!-- Origem -->
                            <v-col cols="12" md="6" class="pr-md-3">
                                <div class="font-weight-medium body-2 primary--text mb-2">
                                    <v-icon small color="primary">mdi-arrow-up-circle-outline</v-icon> Unidade de Origem
                                </div>
                                <v-row dense>
                                    <v-col cols="12">
                                        <v-autocomplete label="Unidade Solicitante*" :items="unidadesSolicitantes"
                                            item-value="UNIDADE_ID" item-text="UNIDADE_NOME" outlined dense hide-details
                                            v-model="chamado.UNIDADE_ID_SOLICITANTE"
                                            @change="verificarDuplicidade"
                                            :menu-props="{ offsetY: true }"></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-text-field label="Profissional Solicitante*" autocomplete="off" outlined dense 
                                        hide-details v-model="chamado.CHAMADO_PROFISSIONAL_SOLICITANTE"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-text-field label="Setor Solicitante*" autocomplete="off" outlined dense
                                            hide-details v-model="chamado.CHAMADO_SETOR_SOLICITANTE"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-text-field label="Leito Solicitante*" autocomplete="off" outlined dense
                                            hide-details v-model="chamado.CHAMADO_LEITO_SOLICITANTE"></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-col>

                            <!-- Destino -->
                            <v-col cols="12" md="6" class="pl-md-3 mt-3 mt-md-0">
                                <div class="font-weight-medium body-2 deep-orange--text mb-2">
                                    <v-icon small color="deep-orange">mdi-arrow-down-circle-outline</v-icon> Unidade de
                                    Destino
                                </div>
                                <v-row dense>
                                    <v-col cols="12">
                                        <v-autocomplete label="Unidade Destino*" :items="unidadesDestino"
                                            item-value="UNIDADE_ID" item-text="UNIDADE_NOME" outlined dense hide-details
                                            v-model="chamado.UNIDADE_ID_DESTINO"
                                            :menu-props="{ offsetY: true }"></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-text-field label="Setor Destino*" autocomplete="off" outlined dense
                                            hide-details v-model="chamado.CHAMADO_SETOR_DESTINO"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6">
                                        <v-text-field label="Leito Destino" autocomplete="off" outlined dense
                                            hide-details v-model="chamado.CHAMADO_LEITO_DESTINO"></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-row>

                        <v-divider class="my-3"></v-divider>

                        <!-- 2.3 Quadro Clínico e Procedimentos -->
                        <div class="caption font-weight-bold grey--text text--darken-2 mb-2">QUADRO CLÍNICO &
                            PROCEDIMENTO</div>
                        <v-row dense class="mb-2">
                            <v-col cols="12" md="6">
                                <v-select label="Procedimento a ser realizado*" :items="procedimentos"
                                    item-value="PROCEDIMENTO_ID" item-text="PROCEDIMENTO_DESCRICAO" outlined dense
                                    hide-details v-model="chamado.PROCEDIMENTO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select label="Diagnóstico*" :items="diagnosticos" item-value="DIAGNOSTICO_ID"
                                    item-text="DIAGNOSTICO_DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.DIAGNOSTICO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" md="9">
                                <v-text-field label="Dispositivos (sondas, drenos e/ou cateteres)*" autocomplete="off"
                                    outlined dense hide-details v-model="chamado.CHAMADO_DISPOSITIVOS"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="3">
                                <v-text-field label="Peso (kg)*" type="number" step="0.01" min="0" outlined dense
                                    hide-details v-model="chamado.CHAMADO_PESO"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-divider class="my-3"></v-divider>

                        <!-- 2.4 Suporte e Sinais Vitais -->
                        <div class="caption font-weight-bold grey--text text--darken-2 mb-2">SINAIS VITAIS & SUPORTE
                        </div>
                        <v-row dense class="mb-2">
                            <v-col cols="12" sm="4" md="4">
                                <v-select label="Tipo de Precaução*" :items="tiposPrecaucao" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_TIPO_PRECAUCAO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" sm="4" md="4">
                                <v-select label="Suporte Hemodinâmico*" :items="suportesHemodinamicos"
                                    item-value="COLUNA_ID" item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_SUPORTE_HEMODINAMICO_ID"></v-select>
                            </v-col>
                            <v-col cols="12" sm="4" md="4">
                                <v-select label="Suporte O2*" :items="suportesO2" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_SUPORTE_O2_ID"></v-select>
                            </v-col>
                            <v-col cols="6" sm="3" md="3">
                                <v-select label="Temperatura*" :items="temperaturas" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_TEMPERATURA_ID"></v-select>
                            </v-col>
                            <v-col cols="6" sm="3" md="3">
                                <v-select label="Pressão Arterial*" :items="pressoesArteriais" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_PRESSAO_ARTERIAL_ID"></v-select>
                            </v-col>
                            <v-col cols="6" sm="3" md="3">
                                <v-select label="Freq. Cardíaca*" :items="frequenciasCardiacas" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_FREQUENCIA_CARDIACA_ID"></v-select>
                            </v-col>
                            <v-col cols="6" sm="3" md="3">
                                <v-select label="Saturação O2*" :items="saturacoes" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable outlined dense hide-details
                                    v-model="chamado.TG_SATURACAO_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-divider class="my-3"></v-divider>

                        <!-- 2.5 Observações -->
                        <v-row dense>
                            <v-col cols="12">
                                <v-textarea label="Observações Complementares" rows="5" auto-grow outlined dense
                                    hide-details v-model="chamado.CHAMADO_OBSERVACAO"></v-textarea>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider></v-divider>

                    <v-card-actions class="pa-3 bg-light">
                        <v-spacer></v-spacer>
                        <v-btn color="grey lighten-1" text tile @click="limpar" class="mr-2">
                            Cancelar
                        </v-btn>
                        <v-btn color="primary" tile min-width="150" :loading="salvando" @click="salvar">
                            <v-icon left>mdi-check</v-icon> Abrir Chamado
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-card-text>
        </v-card>

        <MdNovoPaciente @salvo="pacienteSalvo"></MdNovoPaciente>
    </div>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import MdNovoPaciente from "../paciente/MdNovoPaciente";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "ChamadoView",
    components: { MdNovoPaciente, TratarErroAjax },
    mixins: [UtilsMixins],

    props: {
        sexos: { type: Array, default: () => [] },
        prioridades: { type: Array, default: () => [] },
        tiposChamado: { type: Array, default: () => [] },
        tiposPrecaucao: { type: Array, default: () => [] },
        suportesO2: { type: Array, default: () => [] },
        suportesHemodinamicos: { type: Array, default: () => [] },
        temperaturas: { type: Array, default: () => [] },
        frequenciasCardiacas: { type: Array, default: () => [] },
        pressoesArteriais: { type: Array, default: () => [] },
        saturacoes: { type: Array, default: () => [] },
        unidadesSolicitantes: { type: Array, default: () => [] },
        unidadesDestino: { type: Array, default: () => [] },
        procedimentos: { type: Array, default: () => [] },
        diagnosticos: { type: Array, default: () => [] }
    },

    data() {
        return {
            msgId: "msgChamadoView",
            msgIdDebug: "msgChamadoViewDebug",
            consultandoPaciente: false,
            salvando: false,
            duplicidadeConfirmada: false,
            duplicidadeChave: null,
            dataSolicitacao: moment().format("DD/MM/YYYY"),
            horaSolicitacao: moment().format("HH:mm")
        };
    },

    mounted() {
        this.$store.dispatch("DominioModule/setSexos", this.sexos);
        this.$store.dispatch("ChamadoViewModule/clear");
        this.preencherPadroes();
    },

    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),

        chamado: {
            get() {
                return this.$store.getters["ChamadoViewModule/getChamado"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoViewModule/setChamado", newValue);
            }
        },

        paciente: {
            get() {
                return this.$store.getters["ChamadoViewModule/getPaciente"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoViewModule/setPaciente", newValue);
            }
        },

        pacienteTemporario: {
            get() {
                return this.$store.getters["ChamadoViewModule/getPacienteTemporario"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoViewModule/setPacienteTemporario", newValue);
            }
        },

        pacienteVulnerabilidadeSocial: {
            get() {
                return this.$store.getters["ChamadoViewModule/getPacienteVulnerabilidadeSocial"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoViewModule/setPacienteVulnerabilidadeSocial", newValue);
            }
        },

        cpfPesquisa: {
            get() {
                return this.$store.getters["ChamadoViewModule/getCpfPesquisa"];
            },
            set(newValue) {
                this.$store.dispatch("ChamadoViewModule/setCpfPesquisa", newValue);
            }
        },

        pacienteDisponivel() {
            if (this.pacienteVulnerabilidadeSocial) return !!this.pacienteTemporario.TG_SEXO_ID;
            return !!this.paciente;
        },

        formularioChamadoDisponivel() {
            return this.pacienteVulnerabilidadeSocial || !!this.paciente;
        }
    },

        methods: {
        preencherPadroes() {
            if (this.tiposChamado.length === 1) this.chamado.TG_CHAMADO_ID = this.tiposChamado[0].COLUNA_ID;
            if (this.unidadesSolicitantes.length === 1) this.chamado.UNIDADE_ID_SOLICITANTE = this.unidadesSolicitantes[0].UNIDADE_ID;
        },

        verificarDuplicidade() {
            if (this.pacienteVulnerabilidadeSocial || !this.paciente || !this.chamado.UNIDADE_ID_SOLICITANTE) return;

            let chave = `${this.paciente.PACIENTE_ID}:${this.chamado.UNIDADE_ID_SOLICITANTE}`;
            if (this.duplicidadeChave === chave && this.duplicidadeConfirmada) return;

            axios.get(`${this.baseUrl}/chamado/verificar-duplicidade`, {
                params: {
                    PACIENTE_ID: this.paciente.PACIENTE_ID,
                    UNIDADE_ID_SOLICITANTE: this.chamado.UNIDADE_ID_SOLICITANTE
                }
            }).then(r => {
                if (!r.data.retorno) {
                    this.duplicidadeChave = chave;
                    this.duplicidadeConfirmada = false;
                    return;
                }

                this.exibirAlertaDuplicidade(r.data.retorno, chave);
            }).catch(e => {
                console.error("ERRO AO CONSULTAR DUPLICIDADE: ", e);
            });
        },

        exibirAlertaDuplicidade(duplicidade, chave, aoConfirmar = null) {
            Swal.fire({
                title: "Possível duplicidade",
                text: `Já existe o chamado Nº ${duplicidade.CHAMADO_ID} para este paciente hoje nesta unidade, com situação ${duplicidade.SITUACAO_DESCRICAO}. Deseja continuar com a abertura?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sim, continuar",
                cancelButtonText: "Não, cancelar",
                confirmButtonColor: "#1976D2",
                cancelButtonColor: "#757575"
            }).then(result => {
                if (result.isConfirmed) {
                    this.duplicidadeChave = chave;
                    this.duplicidadeConfirmada = true;
                    if (aoConfirmar) aoConfirmar();
                } else {
                    this.duplicidadeChave = null;
                    this.duplicidadeConfirmada = false;
                    this.limpar();
                }
            });
        },

        alterarTipoPaciente() {
            this.cpfPesquisa = null;
            this.paciente = null;
            this.pacienteTemporario = null;
            this.chamado.PACIENTE_ID = null;
        },

        cpfValido(cpf) {
            let numeros = cpf ? cpf.replace(/\D/g, "") : "";

            if (numeros.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(numeros)) return false;

            let calcularDigito = tamanho => {
                let soma = 0;
                let peso = tamanho + 1;

                for (let i = 0; i < tamanho; i++) soma += Number(numeros[i]) * (peso - i);

                let resto = (soma * 10) % 11;
                if (resto === 10) resto = 0;

                return resto === Number(numeros[tamanho]);
            };

            return calcularDigito(9) && calcularDigito(10);
        },

        buscarPaciente() {
            let cpf = this.cpfPesquisa ? this.cpfPesquisa.replace(/\D/g, "") : null;

            if (!this.cpfValido(cpf)) {
                Swal.fire("Atenção", "Informe um CPF válido para realizar a consulta.", "warning");
                return;
            }

            this.consultandoPaciente = true;
            this.paciente = null;
            this.chamado.PACIENTE_ID = null;
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            axios.get(`${this.baseUrl}/paciente/buscar-por-cpf`, {
                params: { PACIENTE_CPF: cpf }
            }).then(r => {
                if (r.data.retorno) {
                    this.paciente = r.data.retorno;
                    this.chamado.PACIENTE_ID = this.paciente.PACIENTE_ID;
                    this.duplicidadeChave = null;
                    this.duplicidadeConfirmada = false;
                    this.verificarDuplicidade();
                    return;
                }

                Swal.fire({
                    title: "Paciente não cadastrado",
                    text: "O CPF informado não possui paciente cadastrado. Deseja cadastrar agora?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Cadastrar",
                    cancelButtonText: "Cancelar"
                }).then(result => {
                    if (result.isConfirmed) this.abrirCadastroPaciente(cpf);
                });
            }).catch(e => {
                console.error("ERRO: ", e);
                this.$store.dispatch("TratarErroAjaxModule/tratarErro", { id: this.msgId, response: e.response });
            }).finally(() => {
                this.consultandoPaciente = false;
            });
        },

        abrirCadastroPaciente(cpf) {
            this.$store.dispatch("MdNovoPacienteModule/setPaciente", {
                PACIENTE_ID: null,
                PACIENTE_CPF: cpf,
                PACIENTE_NOME: null,
                PACIENTE_DT_NASCIMENTO: null,
                TG_SEXO_ID: null
            });

            this.$store.dispatch("MdNovoPacienteModule/setShowModal", true);
        },

        pacienteSalvo() {
            this.buscarPaciente();
        },

        preencherDadosChamadoTeste() {
            if (this.tiposChamado.length) this.chamado.TG_CHAMADO_ID = this.tiposChamado[0].COLUNA_ID;
            if (this.prioridades.length) this.chamado.TG_PRIORIDADE_ID = this.prioridades[0].COLUNA_ID;
            if (this.unidadesSolicitantes.length) this.chamado.UNIDADE_ID_SOLICITANTE = this.unidadesSolicitantes[0].UNIDADE_ID;
            if (this.unidadesDestino.length) this.chamado.UNIDADE_ID_DESTINO = this.unidadesDestino[0].UNIDADE_ID;
            this.chamado.CHAMADO_PROFISSIONAL_SOLICITANTE = "Dr. João da Silva";
            if (this.procedimentos.length) this.chamado.PROCEDIMENTO_ID = this.procedimentos[0].PROCEDIMENTO_ID;
            if (this.diagnosticos.length) this.chamado.DIAGNOSTICO_ID = this.diagnosticos[0].DIAGNOSTICO_ID;
            if (this.tiposPrecaucao.length) this.chamado.TG_TIPO_PRECAUCAO_ID = this.tiposPrecaucao[0].COLUNA_ID;
            if (this.suportesO2.length) this.chamado.TG_SUPORTE_O2_ID = this.suportesO2[0].COLUNA_ID;
            if (this.suportesHemodinamicos.length) this.chamado.TG_SUPORTE_HEMODINAMICO_ID = this.suportesHemodinamicos[0].COLUNA_ID;
            if (this.temperaturas.length) this.chamado.TG_TEMPERATURA_ID = this.temperaturas[0].COLUNA_ID;
            if (this.frequenciasCardiacas.length) this.chamado.TG_FREQUENCIA_CARDIACA_ID = this.frequenciasCardiacas[0].COLUNA_ID;
            if (this.pressoesArteriais.length) this.chamado.TG_PRESSAO_ARTERIAL_ID = this.pressoesArteriais[0].COLUNA_ID;
            if (this.saturacoes.length) this.chamado.TG_SATURACAO_ID = this.saturacoes[0].COLUNA_ID;

            this.chamado.CHAMADO_HORARIO_ATENDIMENTO = "21:30";
            this.chamado.CHAMADO_AMBULANCIA_EXTRA = 0;
            this.chamado.CHAMADO_SETOR_SOLICITANTE = "UTI";
            this.chamado.CHAMADO_LEITO_SOLICITANTE = "01";
            this.chamado.CHAMADO_SETOR_DESTINO = "CENTRO CIRÚRGICO";
            this.chamado.CHAMADO_LEITO_DESTINO = "02";
            this.chamado.CHAMADO_DISPOSITIVOS = "Cateter venoso central";
            this.chamado.CHAMADO_PESO = 72.5;
            this.chamado.CHAMADO_OBSERVACAO = "Chamado preenchido automaticamente para teste.";
        },

        preencherTesteComCpf() {
            this.$store.dispatch("ChamadoViewModule/clear");
            this.pacienteVulnerabilidadeSocial = false;
            this.cpfPesquisa = "387.999.969-44";
            this.preencherDadosChamadoTeste();
            this.buscarPaciente();
        },

        preencherTesteVulnerabilidade() {
            this.$store.dispatch("ChamadoViewModule/clear");
            this.pacienteVulnerabilidadeSocial = true;

            this.pacienteTemporario = {
                PACIENTE_NOME: "Paciente Vulnerabilidade Teste",
                PACIENTE_DT_NASCIMENTO: "1985-05-20",
                TG_SEXO_ID: this.sexos.length ? this.sexos[0].COLUNA_ID : null
            };

            this.preencherDadosChamadoTeste();
        },

        validarFormulario() {
            if (!this.pacienteDisponivel) {
                Swal.fire("Atenção", this.pacienteVulnerabilidadeSocial
                    ? "Informe o sexo do paciente."
                    : "Consulte e selecione um paciente.", "warning");
                return false;
            }

            if (!this.chamado.TG_CHAMADO_ID) {
                Swal.fire("Atenção", "Informe o tipo de chamado.", "warning");
                return false;
            }

            if (!this.chamado.TG_PRIORIDADE_ID) {
                Swal.fire("Atenção", "Informe a prioridade.", "warning");
                return false;
            }

            if (this.chamado.CHAMADO_AMBULANCIA_EXTRA === null || this.chamado.CHAMADO_AMBULANCIA_EXTRA === undefined) {
                Swal.fire("Atenção", "Informe se necessita de ambulância extra.", "warning");
                return false;
            }

            if (!this.chamado.UNIDADE_ID_SOLICITANTE) {
                Swal.fire("Atenção", "Informe a unidade solicitante.", "warning");
                return false;
            }

            if (!this.chamado.CHAMADO_SETOR_SOLICITANTE) {
                Swal.fire("Atenção", "Informe o setor solicitante.", "warning");
                return false;
            }

            if (!this.chamado.CHAMADO_LEITO_SOLICITANTE) {
                Swal.fire("Atenção", "Informe o leito solicitante.", "warning");
                return false;
            }

            if (!this.chamado.CHAMADO_PROFISSIONAL_SOLICITANTE) {
                Swal.fire("Atenção", "Informe o profissional solicitante.", "warning");
                return false;
            }

            if (!this.chamado.UNIDADE_ID_DESTINO) {
                Swal.fire("Atenção", "Informe a unidade destino.", "warning");
                return false;
            }

            if (!this.chamado.CHAMADO_SETOR_DESTINO) {
                Swal.fire("Atenção", "Informe o setor destino.", "warning");
                return false;
            }

            if (!this.chamado.PROCEDIMENTO_ID) {
                Swal.fire("Atenção", "Informe o procedimento.", "warning");
                return false;
            }

            if (!this.chamado.DIAGNOSTICO_ID) {
                Swal.fire("Atenção", "Informe o diagnóstico.", "warning");
                return false;
            }

            if (!this.chamado.CHAMADO_DISPOSITIVOS) {
                Swal.fire("Atenção", "Informe os dispositivos (sondas, drenos e/ou cateteres).", "warning");
                return false;
            }

            if (this.chamado.CHAMADO_PESO === null || this.chamado.CHAMADO_PESO === undefined || this.chamado.CHAMADO_PESO === "") {
                Swal.fire("Atenção", "Informe o peso.", "warning");
                return false;
            }

            if (!this.chamado.TG_TIPO_PRECAUCAO_ID) {
                Swal.fire("Atenção", "Informe o tipo de precaução.", "warning");
                return false;
            }

            if (!this.chamado.TG_SUPORTE_HEMODINAMICO_ID) {
                Swal.fire("Atenção", "Informe o suporte hemodinâmico.", "warning");
                return false;
            }

            if (!this.chamado.TG_SUPORTE_O2_ID) {
                Swal.fire("Atenção", "Informe o suporte de O2.", "warning");
                return false;
            }

            if (!this.chamado.TG_TEMPERATURA_ID) {
                Swal.fire("Atenção", "Informe a temperatura.", "warning");
                return false;
            }

            if (!this.chamado.TG_PRESSAO_ARTERIAL_ID) {
                Swal.fire("Atenção", "Informe a pressão arterial.", "warning");
                return false;
            }

            if (!this.chamado.TG_FREQUENCIA_CARDIACA_ID) {
                Swal.fire("Atenção", "Informe a frequência cardíaca.", "warning");
                return false;
            }

            if (!this.chamado.TG_SATURACAO_ID) {
                Swal.fire("Atenção", "Informe a saturação de O2.", "warning");
                return false;
            }

            return true;
        },

        salvar(confirmarDuplicidade = false) {
            if (!this.validarFormulario()) return;

            this.salvando = true;
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            let payload = JSON.parse(JSON.stringify(this.chamado));
            payload.CONFIRMAR_DUPLICIDADE = Boolean(confirmarDuplicidade || this.duplicidadeConfirmada);

            if (this.pacienteVulnerabilidadeSocial) {
                payload.PACIENTE_ID = null;
                payload.PACIENTE_VULNERABILIDADE_SOCIAL = 1;
                payload.PACIENTE_NOME = this.pacienteTemporario ? this.pacienteTemporario.PACIENTE_NOME : null;
                payload.PACIENTE_DT_NASCIMENTO = this.pacienteTemporario ? this.pacienteTemporario.PACIENTE_DT_NASCIMENTO : null;
                payload.TG_SEXO_ID = this.pacienteTemporario ? this.pacienteTemporario.TG_SEXO_ID : null;
            } else {
                // Garante que o PACIENTE_ID venha do objeto paciente localizado
                payload.PACIENTE_ID = this.paciente ? this.paciente.PACIENTE_ID : this.chamado.PACIENTE_ID;
                payload.PACIENTE_VULNERABILIDADE_SOCIAL = 0;
            }

            axios.post(`${this.baseUrl}/chamado/abrir`, payload)
                .then(r => {
                    if (r.data.cod === 2) {
                        this.salvando = false;
                        let chave = `${payload.PACIENTE_ID}:${payload.UNIDADE_ID_SOLICITANTE}`;
                        this.exibirAlertaDuplicidade(r.data.retorno, chave, () => this.salvar(true));
                        return;
                    }

                    Swal.fire("Sucesso", r.data.msg || "Chamado aberto com sucesso.", "success").then(() => {
                        this.limpar();
                    });
                })
                .catch(e => {
                    console.error("ERRO AO ABRIR CHAMADO: ", e);
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response
                    });
                })
                .finally(() => {
                    this.salvando = false;
                });
        },

        limpar() {
            this.$store.dispatch("ChamadoViewModule/clear");
            this.duplicidadeChave = null;
            this.duplicidadeConfirmada = false;
            this.dataSolicitacao = moment().format("DD/MM/YYYY");
            this.horaSolicitacao = moment().format("HH:mm");
            this.preencherPadroes();
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
        },

        descricaoTabelaGenerica(lista, colunaId) {
            let item = lista.find(item => Number(item.COLUNA_ID) === Number(colunaId));
            return item ? item.DESCRICAO : "-";
        },

        calcularIdade(data) {
            if (!data) return "-";
            return moment().diff(moment(data), "years");
        }
    }
}
</script>

<style scoped></style>
