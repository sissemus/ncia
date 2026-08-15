<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-ambulance</v-icon>
                <v-toolbar-title>Abrir Chamado</v-toolbar-title>
                <v-spacer></v-spacer>

                <v-btn color="secondary" outlined tile class="mr-2" @click="preencherTesteComCpf">
                    teste com CPF
                </v-btn>

                <v-btn color="secondary" outlined tile class="mr-2" @click="preencherTesteVulnerabilidade">
                    teste vulnerabilidade
                </v-btn>

                <v-btn color="red" dark outlined tile @click="limpar">
                    limpar
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-card outlined class="mb-5">
                    <v-card-title class="subtitle-1 font-weight-bold">Paciente</v-card-title>

                    <v-card-text>
                        <v-row>
                            <v-col cols="12">
                                <v-checkbox label="Paciente em vulnerabilidade social" hide-details
                                    v-model="pacienteVulnerabilidadeSocial" @change="alterarTipoPaciente"></v-checkbox>
                            </v-col>
                        </v-row>

                        <template v-if="!pacienteVulnerabilidadeSocial">
                            <v-row align="center">
                                <v-col cols="12" md="4">
                                    <v-text-field label="CPF*" v-mask="'###.###.###-##'" autocomplete="off" hide-details
                                        v-model="cpfPesquisa" @keyup.enter="buscarPaciente">
                                        <template v-slot:append>
                                            <v-icon @click="buscarPaciente">mdi-magnify</v-icon>
                                        </template>
                                    </v-text-field>
                                </v-col>

                                <v-col cols="12" md="2">
                                    <v-btn block color="primary" tile :loading="consultandoPaciente"
                                        @click="buscarPaciente">
                                        consultar
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" md="6" v-if="paciente">
                                    <v-alert type="success" text dense class="ma-0">
                                        Paciente localizado.
                                    </v-alert>
                                </v-col>
                            </v-row>

                            <v-row v-if="paciente" class="mt-3">
                                <v-col cols="12" md="5">
                                    <v-text-field label="Nome Completo" readonly hide-details
                                        :value="paciente.PACIENTE_NOME"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="3">
                                    <v-text-field label="CPF" readonly hide-details
                                        :value="paciente.PACIENTE_CPF"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="2">
                                    <v-text-field label="Nascimento" readonly hide-details
                                        :value="formatarDataBR(paciente.PACIENTE_DT_NASCIMENTO)"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="1">
                                    <v-text-field label="Idade" readonly hide-details
                                        :value="calcularIdade(paciente.PACIENTE_DT_NASCIMENTO)"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="1">
                                    <v-text-field label="Sexo" readonly hide-details
                                        :value="descricaoTabelaGenerica(sexos, paciente.TG_SEXO_ID)"></v-text-field>
                                </v-col>
                            </v-row>
                        </template>

                        <template v-else>
                            <v-alert type="info" text dense class="mt-4">
                                Este paciente será cadastrado somente para este chamado e não aparecerá na lista geral
                                de pacientes.
                            </v-alert>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        :label="pacienteVulnerabilidadeSocial ? 'Nome Completo (opcional)' : 'Nome Completo*'"
                                        autocomplete="off" v-model="pacienteTemporario.PACIENTE_NOME"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="3">
                                    <v-text-field
                                        :label="pacienteVulnerabilidadeSocial ? 'Data de Nascimento (opcional)' : 'Data de Nascimento*'"
                                        type="date" autocomplete="off"
                                        v-model="pacienteTemporario.PACIENTE_DT_NASCIMENTO"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="3">
                                    <v-select label="Sexo*" :items="sexos" item-value="COLUNA_ID" item-text="DESCRICAO"
                                        v-model="pacienteTemporario.TG_SEXO_ID"></v-select>
                                </v-col>
                            </v-row>
                        </template>
                    </v-card-text>
                </v-card>

                <v-card outlined :disabled="!pacienteDisponivel">
                    <v-card-title class="subtitle-1 font-weight-bold">Dados do Chamado</v-card-title>

                    <v-card-text>
                        <v-row>
                            <v-col cols="12" md="2">
                                <v-text-field label="Data da Solicitação" readonly hide-details
                                    :value="dataSolicitacao"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="2">
                                <v-text-field label="Hora" readonly hide-details
                                    :value="horaSolicitacao"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-select label="Tipo de Chamado*" :items="tiposChamado" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" hide-details v-model="chamado.TG_CHAMADO_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-select label="Prioridade*" :items="prioridades" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" hide-details v-model="chamado.TG_PRIORIDADE_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="2">
                                <v-text-field label="Horário de Atendimento" type="time" hide-details
                                    v-model="chamado.CHAMADO_HORARIO_ATENDIMENTO"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-divider class="my-5"></v-divider>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select label="Unidade Solicitante*" :items="unidadesSolicitantes"
                                    item-value="UNIDADE_ID" item-text="UNIDADE_NOME" hide-details
                                    v-model="chamado.UNIDADE_ID_SOLICITANTE"></v-select>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-text-field label="Setor Solicitante" autocomplete="off" hide-details
                                    v-model="chamado.CHAMADO_SETOR_SOLICITANTE"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-text-field label="Leito Solicitante" autocomplete="off" hide-details
                                    v-model="chamado.CHAMADO_LEITO_SOLICITANTE"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select label="Profissional Solicitante*" :items="profissionais"
                                    item-value="PROFISSIONAL_ID" item-text="PROFISSIONAL_NOME" hide-details
                                    v-model="chamado.PROFISSIONAL_ID_SOLICITANTE"></v-select>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-select label="Unidade Destino*" :items="unidadesDestino" item-value="UNIDADE_ID"
                                    item-text="UNIDADE_NOME" hide-details
                                    v-model="chamado.UNIDADE_ID_DESTINO"></v-select>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field label="Setor Destino" autocomplete="off" hide-details
                                    v-model="chamado.CHAMADO_SETOR_DESTINO"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-text-field label="Leito Destino" autocomplete="off" hide-details
                                    v-model="chamado.CHAMADO_LEITO_DESTINO"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-divider class="my-5"></v-divider>

                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select label="Procedimento a ser realizado*" :items="procedimentos"
                                    item-value="PROCEDIMENTO_ID" item-text="PROCEDIMENTO_DESCRICAO" hide-details
                                    v-model="chamado.PROCEDIMENTO_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-select label="Diagnóstico" :items="diagnosticos" item-value="DIAGNOSTICO_ID"
                                    item-text="DIAGNOSTICO_DESCRICAO" clearable hide-details
                                    v-model="chamado.DIAGNOSTICO_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" md="9">
                                <v-text-field label="Dispositivos (sondas, drenos e/ou cateteres)" autocomplete="off"
                                    hide-details v-model="chamado.CHAMADO_DISPOSITIVOS"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-text-field label="Peso (kg)" type="number" step="0.01" min="0" hide-details
                                    v-model="chamado.CHAMADO_PESO"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-divider class="my-5"></v-divider>

                        <v-row>
                            <v-col cols="12" md="4">
                                <v-select label="Tipo de Precaução" :items="tiposPrecaucao" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_TIPO_PRECAUCAO_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="4">
                                <v-select label="Suporte Hemodinâmico" :items="suportesHemodinamicos"
                                    item-value="COLUNA_ID" item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_SUPORTE_HEMODINAMICO_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="4">
                                <v-select label="Suporte O2" :items="suportesO2" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_SUPORTE_O2_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" md="3">
                                <v-select label="Temperatura" :items="temperaturas" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_TEMPERATURA_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-select label="Pressão Arterial" :items="pressoesArteriais" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_PRESSAO_ARTERIAL_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-select label="Frequência Cardíaca" :items="frequenciasCardiacas"
                                    item-value="COLUNA_ID" item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_FREQUENCIA_CARDIACA_ID"></v-select>
                            </v-col>

                            <v-col cols="12" md="3">
                                <v-select label="Saturação O2" :items="saturacoes" item-value="COLUNA_ID"
                                    item-text="DESCRICAO" clearable hide-details
                                    v-model="chamado.TG_SATURACAO_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12">
                                <v-textarea label="Observação" rows="3" auto-grow outlined
                                    v-model="chamado.CHAMADO_OBSERVACAO"></v-textarea>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn color="primary" tile :loading="salvando" @click="salvar">
                            abrir chamado
                        </v-btn>

                        <v-btn color="red" dark outlined tile @click="limpar">
                            limpar
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
        profissionais: { type: Array, default: () => [] },
        procedimentos: { type: Array, default: () => [] },
        diagnosticos: { type: Array, default: () => [] }
    },

    data() {
        return {
            msgId: "msgChamadoView",
            msgIdDebug: "msgChamadoViewDebug",
            consultandoPaciente: false,
            salvando: false,
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
        }
    },

    methods: {
        preencherPadroes() {
            if (this.tiposChamado.length === 1) this.chamado.TG_CHAMADO_ID = this.tiposChamado[0].COLUNA_ID;
            if (this.unidadesSolicitantes.length === 1) this.chamado.UNIDADE_ID_SOLICITANTE = this.unidadesSolicitantes[0].UNIDADE_ID;
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
            if (this.profissionais.length) this.chamado.PROFISSIONAL_ID_SOLICITANTE = this.profissionais[0].PROFISSIONAL_ID;
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

            if (!this.chamado.UNIDADE_ID_SOLICITANTE) {
                Swal.fire("Atenção", "Informe a unidade solicitante.", "warning");
                return false;
            }

            if (!this.chamado.PROFISSIONAL_ID_SOLICITANTE) {
                Swal.fire("Atenção", "Informe o profissional solicitante.", "warning");
                return false;
            }

            if (!this.chamado.UNIDADE_ID_DESTINO) {
                Swal.fire("Atenção", "Informe a unidade destino.", "warning");
                return false;
            }

            if (!this.chamado.PROCEDIMENTO_ID) {
                Swal.fire("Atenção", "Informe o procedimento.", "warning");
                return false;
            }

            return true;
        },

        salvar() {
            if (!this.validarFormulario()) return;

            this.salvando = true;
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            let payload = JSON.parse(JSON.stringify(this.chamado));

            if (this.pacienteVulnerabilidadeSocial) {
                payload.PACIENTE_ID = null;
                payload.PACIENTE_VULNERABILIDADE_SOCIAL = 1;
                payload.PACIENTE_NOME = this.pacienteTemporario.PACIENTE_NOME;
                payload.PACIENTE_DT_NASCIMENTO = this.pacienteTemporario.PACIENTE_DT_NASCIMENTO;
                payload.TG_SEXO_ID = this.pacienteTemporario.TG_SEXO_ID;
            } else {
                payload.PACIENTE_ID = this.paciente.PACIENTE_ID;
                payload.PACIENTE_VULNERABILIDADE_SOCIAL = 0;
            }

            axios.post(`${this.baseUrl}/chamado/abrir`, payload).then(() => {
                Swal.fire("Sucesso", "Chamado aberto com sucesso.", "success").then(() => {
                    this.limpar();
                });
            }).catch(e => {
                console.error("ERRO: ", e);
                this.$store.dispatch("TratarErroAjaxModule/tratarErro", { id: this.msgId, response: e.response });
            }).finally(() => {
                this.salvando = false;
            });
        },

        limpar() {
            this.$store.dispatch("ChamadoViewModule/clear");
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