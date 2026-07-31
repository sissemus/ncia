<template>
    <v-dialog v-model="showModal" persistent width="900" scrollable :fullscreen="fullScreen">
        <v-card>
            <v-toolbar light elevation="1" class="flex-grow-0" color="primary" dark>
                <v-toolbar-title>Detalhes do Profissional</v-toolbar-title>
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
                <v-row>
                    <v-col cols="12" md="8">
                        <v-text-field label="Nome*" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_NOME"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-text-field label="CPF*" v-mask="'###.###.###-##'" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_CPF"></v-text-field>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field label="Data de Nascimento*" type="date" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_NASCIMENTO"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-select label="Sexo*" :items="sexos" item-value="COLUNA_ID" item-text="DESCRICAO"
                            clearable v-model="profissional.TG_SEXO_ID"></v-select>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-select label="Tipo de Profissional*" :items="tiposProfissional"
                            item-value="COLUNA_ID" item-text="DESCRICAO"
                            v-model="profissional.TG_TIPO_PROFISSIONAL_ID"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" md="4">
                        <v-select label="Ativo*" :items="ativos" item-value="id" item-text="text"
                            v-model="profissional.PROFISSIONAL_ATIVO"></v-select>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-divider class="ma-0"></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark tile @click="salvar">
                    Salvar Profissional
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "MdNovoProfissional",
    components: { TratarErroAjax },
    mixins: [UtilsMixins],
    props: {
        sexos: {
            type: Array,
            default: () => []
        },
        tiposProfissional: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            msgId: "msgMdNovoProfissional",
            msgIdDebug: "msgMdNovoProfissionalDebug",
            ativos: [
                { id: 1, text: "Sim" },
                { id: 0, text: "Não" }
            ]
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),
        profissional: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getProfissional"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setProfissional", newValue)
            }
        },
        showModal: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getShowModal"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setShowModal", newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getFullScreen"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setFullScreen", newValue)
            }
        }
    },
    methods: {
        salvar() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId)

            let payload = JSON.parse(JSON.stringify(this.profissional))

            if (payload.PROFISSIONAL_CPF)
                payload.PROFISSIONAL_CPF = payload.PROFISSIONAL_CPF.replace(/\D/g, "")

            payload.PROFISSIONAL_NASCIMENTO = this.formatarDataSQL(
                payload.PROFISSIONAL_NASCIMENTO
            )

            axios({
                method: payload.PROFISSIONAL_ID === null ? "POST" : "PUT",
                url: payload.PROFISSIONAL_ID === null
                    ? `${this.baseUrl}/profissional/inserir`
                    : `${this.baseUrl}/profissional/alterar`,
                data: payload
            })
                .then(() => {
                    Swal.fire("Sucesso", "Profissional salvo com sucesso", "success")
                    this.clearFormAndClose()
                    this.$emit("salvo")
                })
                .catch(e => {
                    console.error("ERRO: ", e)
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response
                    })
                })
        },

        clearForm() {
            this.$store.dispatch("MdNovoProfissionalModule/setProfissional", null)
        },

        clearFormAndClose() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId)
            this.clearForm()
            this.fullScreen = false
            this.showModal = false
        }
    }
}
</script>

<style scoped></style>