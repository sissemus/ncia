<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="900" scrollable :fullScreen="fullScreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0 mb-3">
                        <v-toolbar-title>Selecionar Perfil</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose(false)">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>

                    <v-card-text>
                        <v-row>
                            <v-col>
                                <v-text-field
                                    label="Pesquisar"
                                    autocomplete="off"
                                    placeholder="Digite um valor de pesquisa"
                                    hide-details
                                    append-icon="mdi-magnify"
                                    v-model="valorPesquisa

"
                                    @keypress.enter="pesquisar"
                                    @click:append="pesquisar"
                                ></v-text-field>
                            </v-col>
                        </v-row>

                        <v-simple-table dense v-show="perfis.length" class="mb-0">
                            <template v-slot:default>
                                <thead>
                                <tr>
                                    <th class="text-left">Id</th>
                                    <th class="text-left">Nome</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="perfil in perfis"
                                    :key="perfil['PERFIL_ID']"
                                    @click="selecionar(perfil)"
                                    style="cursor: pointer"
                                >
                                    <td>{{ perfil["PERFIL_ID"] }}</td>
                                    <td>{{ perfil["PERFIL_NOME"] }}</td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>

                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark outlined tile @click="clearFormAndClose">
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";
    import TratarErroAjax from "../assets/TratarErroAjax";

    export default {
        name: "MdSelecionarPerfil",
        components: {TratarErroAjax},
        data() {
            return {
                msgId: 'msgMdSelecionarPerfil',
                msgIdDebug: 'msgMdSelecionarPerfilDebug',
                valorPesquisa: "",
            }
        },
        computed: {
            ...mapGetters({
                baseUrl: "getBaseUrl",
            }),
            showModal: {
                get() {
                    return this.$store.getters["MdSelecionarPerfilModule/getShowModal"]
                },
                set(newValue) {
                    this.$store.dispatch("MdSelecionarPerfilModule/setShowModal", newValue)
                },
            },
            fullScreen: {
                get() {
                    return this.$store.getters["MdSelecionarPerfilModule/getFullScreen"]
                },
                set(newValue) {
                    this.$store.dispatch("MdSelecionarPerfilModule/setFullScreen", newValue)
                },
            },
            perfis: {
                get() {
                    return this.$store.getters["MdSelecionarPerfilModule/getPerfis"]
                },
                set(newValue) {
                    this.$store.dispatch("MdSelecionarPerfilModule/setPerfis", newValue)
                },
            },
            quemChamou: {
                get() {
                    return this.$store.getters["MdSelecionarPerfilModule/getQuemChamou"]
                },
                set(newValue) {
                    this.$store.dispatch("MdSelecionarPerfilModule/setQuemChamou", newValue)
                },
            },
            modulo: {
                get() {
                    return this.$store.getters[
                        "MdSelecionarPerfilModule/getModulo"
                        ];
                },
                set(newValue) {
                    this.$store.dispatch(
                        "MdSelecionarPerfilModule/setModulo",
                        newValue
                    );
                },
            },
        },
        methods: {
            clearFormAndClose() {
                this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
                this.perfis = [];
                this.quemChamou = "";
                this.modulo = null;
                this.valorPesquisa = "";
                this.showModal = false;
            },
            pesquisar() {
                this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
                axios({
                    method: "GET",
                    url: `${this.baseUrl}/perfil/search`,
                    // data:this.valorPesquisa
                    params: {
                        valorPesquisa: this.valorPesquisa
                    }
                })
                    .then((r) => {
                        this.perfis = r.data.data;
                        this.pagination = r.data;
                    })
                    .catch((e) => {
                        console.error("ERRO: ", e);
                        this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                            id: this.msgId,
                            response: e.response,
                        });
                    });
            },

            selecionar(perfil) {
                if (this.modulo) {
                    this.$store.dispatch(this.modulo, perfil);
                }
                this.clearFormAndClose();
            },
        }
    }
</script>

<style scoped>

</style>
