<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="900" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes do Paciente</v-toolbar-title>
                        <v-spacer></v-spacer>

                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>

                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>

                        <v-btn icon @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>

                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>

                    <v-card-text class="mt-5">
                        <v-alert v-if="paciente.PACIENTE_CODIGO_TEMPORARIO" type="info" text>
                            Paciente temporário:
                            <strong>{{ paciente.PACIENTE_CODIGO_TEMPORARIO }}</strong>
                        </v-alert>

                        <v-row>
                            <v-col cols="12">
                                <v-checkbox label="Paciente em situação de vulnerabilidade social"
                                    v-model="paciente.PACIENTE_VULNERABILIDADE_SOCIAL" :true-value="1" :false-value="0"
                                    :disabled="cpfBloqueado" @change="alterarVulnerabilidade"></v-checkbox>

                                <small v-if="cpfBloqueado" class="grey--text">
                                    A condição não pode ser alterada porque o paciente já possui CPF cadastrado.
                                </small>
                            </v-col>
                        </v-row>

                        <template v-if="paciente.PACIENTE_VULNERABILIDADE_SOCIAL !== 1">
                            <v-row>
                                <v-col cols="12" md="8">
                                    <v-text-field label="CPF*" v-mask="'###.###.###-##'" autocomplete="off"
                                        :readonly="cpfBloqueado" v-model="paciente.PACIENTE_CPF">
                                        <template v-slot:append>
                                            <v-icon v-if="!cpfBloqueado && paciente.PACIENTE_CPF"
                                                @click="buscarPorCpf">mdi-magnify</v-icon>
                                        </template>
                                    </v-text-field>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <v-btn block color="primary" outlined class="mt-3"
                                        :disabled="cpfBloqueado || !paciente.PACIENTE_CPF" @click="buscarPorCpf">
                                        Consultar CPF
                                    </v-btn>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="8">
                                    <v-text-field label="Nome Completo*" autocomplete="off"
                                        v-model="paciente.PACIENTE_NOME"></v-text-field>
                                </v-col>

                                <v-col cols="12" md="4">
                                    <v-text-field label="Data de Nascimento*" type="date" autocomplete="off"
                                        v-model="paciente.PACIENTE_DT_NASCIMENTO"></v-text-field>
                                </v-col>
                            </v-row>
                        </template>

                        <v-row>
                            <v-col cols="12">
                                <v-select label="Sexo*" :items="sexos" item-value="COLUNA_ID" item-text="DESCRICAO"
                                    v-model="paciente.TG_SEXO_ID"></v-select>
                            </v-col>
                        </v-row>

                        <v-alert v-if="paciente.PACIENTE_VULNERABILIDADE_SOCIAL === 1" type="warning" text>
                            Para pacientes em situação de vulnerabilidade social, apenas o sexo será
                            obrigatório. O sistema gerará um código temporário para identificação.
                        </v-alert>
                    </v-card-text>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">salvar</v-btn>
                        <v-btn color="red" dark outlined tile @click="clearFormAndClose">fechar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "MdNovoPaciente",
    components: { TratarErroAjax },
    mixins: [UtilsMixins],
    data() {
        return {
            msgId: "msgMdNovoPaciente",
            msgIdDebug: "msgMdNovoPacienteDebug"
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),
        paciente: {
            get() { return this.$store.getters["MdNovoPacienteModule/getPaciente"] },
            set(newValue) { this.$store.dispatch("MdNovoPacienteModule/setPaciente", newValue) }
        },
        showModal: {
            get() { return this.$store.getters["MdNovoPacienteModule/getShowModal"] },
            set(newValue) { this.$store.dispatch("MdNovoPacienteModule/setShowModal", newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters["MdNovoPacienteModule/getFullScreen"] },
            set(newValue) { this.$store.dispatch("MdNovoPacienteModule/setFullScreen", newValue) }
        },
        sexos() {
            return this.$store.getters["DominioModule/getSexos"];
        },
        cpfBloqueado() {
            return this.paciente.PACIENTE_ID !== null && !!this.paciente.PACIENTE_DT_IDENTIFICACAO;
        }
    },
    methods: {
        alterarVulnerabilidade(valor) {
            if (valor !== 1 || this.paciente.PACIENTE_ID !== null) return;

            this.paciente.PACIENTE_CPF = null;
            this.paciente.PACIENTE_NOME = null;
            this.paciente.PACIENTE_DT_NASCIMENTO = null;
        },

        buscarPorCpf() {
            let cpf = this.paciente.PACIENTE_CPF ? this.paciente.PACIENTE_CPF.replace(/\D/g, "") : null;

            if (!cpf || cpf.length !== 11) {
                Swal.fire("Atenção", "Informe um CPF completo para realizar a consulta.", "warning");
                return;
            }

            let pacienteAtual = JSON.parse(JSON.stringify(this.paciente));
            pacienteAtual.PACIENTE_CPF = null;

            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            axios.get(`${this.baseUrl}/paciente/buscar-por-cpf`, {
                params: { PACIENTE_CPF: cpf }
            }).then(r => {
                if (r.data.retorno) {
                    this.paciente = pacienteAtual;

                    Swal.fire(
                        "CPF já cadastrado",
                        "Já existe um paciente cadastrado com o CPF informado.",
                        "warning"
                    );

                    return;
                }

                Swal.fire(
                    "CPF disponível",
                    "O CPF não possui cadastro e poderá ser vinculado a este paciente.",
                    "success"
                );
            }).catch(e => {
                console.error("ERRO: ", e);
                this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                    id: this.msgId,
                    response: e.response
                });
            });
        },

        salvar() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            let payload = JSON.parse(JSON.stringify(this.paciente));

            if (payload.PACIENTE_VULNERABILIDADE_SOCIAL === 1) {
                payload.PACIENTE_CPF = null;
                payload.PACIENTE_NOME = null;
                payload.PACIENTE_DT_NASCIMENTO = null;
            } else {
                if (payload.PACIENTE_CPF)
                    payload.PACIENTE_CPF = payload.PACIENTE_CPF.replace(/\D/g, "");

                if (payload.PACIENTE_DT_NASCIMENTO)
                    payload.PACIENTE_DT_NASCIMENTO = this.formatarDataSQL(payload.PACIENTE_DT_NASCIMENTO);
            }

            axios({
                method: payload.PACIENTE_ID === null ? "POST" : "PUT",
                url: payload.PACIENTE_ID === null
                    ? `${this.baseUrl}/paciente/inserir`
                    : `${this.baseUrl}/paciente/alterar`,
                data: payload
            }).then(() => {
                this.clearFormAndClose();

                Swal.fire("Sucesso", "Paciente salvo com sucesso", "success").then(() => {
                    this.$emit("salvo");
                });
            }).catch(e => {
                console.error("ERRO: ", e);
                this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                    id: this.msgId,
                    response: e.response
                });
            });
        },

        clearForm() {
            this.$store.dispatch("MdNovoPacienteModule/setPaciente", null);
        },

        clearFormAndClose() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
            this.clearForm();
            this.fullScreen = false;
            this.showModal = false;
        }
    }
}
</script>

<style scoped></style>