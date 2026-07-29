<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes da Unidade</v-toolbar-title>
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
                            <v-col cols="8">
                                <v-text-field label="Nome da Unidade*" autocomplete="off"
                                    v-model="unidade.UNIDADE_NOME"></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-select label="Unidade Solicitante*" :items="solicitantes" :item-value="'id'"
                                    :item-text="'text'" v-model="unidade.UNIDADE_SOLICITANTE">
                                </v-select>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">
                            salvar
                        </v-btn>
                        <v-btn color="red" dark outlined tile @click="clearFormAndClose">
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";

export default {
    name: "MdNovoUnidade",
    components: { TratarErroAjax },
    data() {
        return {
            msgId: 'msgMdNovoUnidade',
            msgIdDebug: 'msgMdNovoUnidadeDebug',
            solicitantes: [
                {
                    id: 1,
                    text: 'Sim'
                },
                {
                    id: 0,
                    text: 'Não'
                }
            ],
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
        }),
        showModal: {
            get() {
                return this.$store.getters['MdNovoUnidadeModule/getShowModal']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoUnidadeModule/setShowModal', newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters['MdNovoUnidadeModule/getFullScreen']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoUnidadeModule/setFullScreen', newValue)
            }
        },
        unidade: {
            get() {
                return this.$store.getters['MdNovoUnidadeModule/getUnidade']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoUnidadeModule/setUnidade', newValue)
            }
        }
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.unidade = null
            this.showModal = false
        },

        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)

            axios({
                method: this.unidade.UNIDADE_ID === null ? 'POST' : 'PUT',
                url: this.unidade.UNIDADE_ID === null ? `${this.baseUrl}/unidade/inserir` : `${this.baseUrl}/unidade/alterar`,
                data: this.unidade
            }).then(r => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success').then(r => {
                    this.$store.dispatch('UnidadeViewModule/search', this.msgId)
                })
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        }
    }
}
</script>

<style scoped></style>