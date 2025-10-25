<template>
    <div>
        <v-row justify="center">
            <v-dialog
                v-model="showModal"
                persistent
                width="800"
                scrollable
                :fullscreen="fullScreen"
            >
                <v-card>
                    <v-toolbar
                        light
                        elevation="1"
                        class="flex-grow-0"
                        color="primary"
                        dark
                    >
                        <v-toolbar-title>Detalhes da Aplicação</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn
                            icon
                            @click="fullScreen = true"
                            v-show="fullScreen === false"
                        >
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn
                            icon
                            @click="fullScreen = false"
                            v-show="fullScreen === true"
                        >
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>

                    <perfect-scrollbar>
                        <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                        <div :id="msgIdDebug"></div>
                        <v-card-text>
                            <v-row>
                                <v-col>
                                    <v-select
                                        label="Aplicação pai"
                                        hide-details
                                        :items="aplicacoes"
                                        item-value="APLICACAO_ID"
                                        item-text="APLICACAO_NOME"
                                        v-model="aplicacao.APLICACAO_PAI_ID"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col class="mb--2">
                                    <v-text-field
                                        label="Aplicação Nome*"
                                        autocomplete="off"
                                        hide-details
                                        v-model="aplicacao.APLICACAO_NOME"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col class="mb--2">
                                    <v-text-field
                                        label="Ordem*"
                                        autocomplete="off"
                                        type="number"
                                        hide-details
                                        v-model="aplicacao.APLICACAO_ORDEM"
                                    ></v-text-field>
                                </v-col>
                                <v-col class="mb--2">
                                    <v-text-field
                                        label="Icone"
                                        autocomplete="off"
                                        hide-details
                                        v-model="aplicacao.APLICACAO_ICONE"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col class="mb--2">
                                    <v-text-field
                                        label="Dominio"
                                        autocomplete="off"
                                        readonly
                                        hide-details
                                        :value="baseUrl"
                                        append-icon="mdi-lock"
                                    ></v-text-field>
                                </v-col>
                                <v-col class="mb--2">
                                    <v-text-field
                                        label="URL"
                                        autocomplete="off"
                                        hide-details
                                        v-model="aplicacao.APLICACAO_URL"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col>
                                    <v-select
                                        label="Gestão*"
                                        :items="ativos"
                                        :item-value="'id'"
                                        :item-text="'text'"
                                        v-model="aplicacao.APLICACAO_GESTAO"
                                    >
                                    </v-select>
                                </v-col>
                                <v-col>
                                    <v-select
                                        label="Ativo*"
                                        :items="ativos"
                                        :item-value="'id'"
                                        :item-text="'text'"
                                        v-model="aplicacao.APLICACAO_ATIVA"
                                    >
                                    </v-select>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </perfect-scrollbar>
                    <v-divider class="ma-0"></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">
                            salvar Aplicação
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import {mapGetters} from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";

export default {
    name: "MdNovaAplicacao",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: "msgAplicacaoView",
            msgIdDebug: "msgAplicacaoViewDebug",
            ativos: [
                {
                    id: 1,
                    text: "Sim",
                },
                {
                    id: 0,
                    text: "Não",
                },
            ],
        };
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl",
            aplicacoes: "AplicacaoViewModule/getAplicacoes",
        }),
        aplicacao: {
            get() {
                return this.$store.getters["MdNovaAplicacaoModule/getAplicacao"];
            },
            set(newValue) {
                this.$store.dispatch("MdNovaAplicacaoModule/setAplicacao", newValue);
            },
        },
        aplicacoes: {
            get() {
                return this.$store.getters['AplicacaoViewModule/getAplicacoes'];
            },
            set(value) {
                this.$store.dispatch('AplicacaoViewModule/setAplicacoes', value);
            },
        },
        showModal: {
            get() {
                return this.$store.getters["MdNovaAplicacaoModule/getShowModal"];
            },
            set(newValue) {
                this.$store.dispatch("MdNovaAplicacaoModule/setShowModal", newValue);
            },
        },
        fullScreen: {
            get() {
                return this.$store.getters["MdNovaAplicacaoModule/getFullScreen"];
            },
            set(newValue) {
                this.$store.dispatch("MdNovaAplicacaoModule/setFullScreen", newValue);
            },
        },
    },
    methods: {
        clearFormAndClose() {
            this.showModal = false;
            this.aplicacao = null;
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
        },
        salvar() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
            axios({
                method: this.aplicacao.APLICACAO_ID === null ? "POST" : "PUT",
                url:
                    this.aplicacao.APLICACAO_ID === null
                        ? `${this.baseUrl}/aplicacao/create`
                        : `${this.baseUrl}/aplicacao/update`,
                data: this.aplicacao,
            })
                .then((r) => {
                    // this.listar(1);
                    this.aplicacoes = r.data;
                    Swal.fire("Sucesso", "Salvo com sucesso", "success");
                    this.clearFormAndClose();
                })
                .catch((e) => {
                    console.error("ERRO: ", e);
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response,
                    });
                });
        },
    },
};
</script>

<style>
</style>
