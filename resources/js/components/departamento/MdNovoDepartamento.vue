<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes do Departamento</v-toolbar-title>
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
                            <v-col cols="6">
                                <v-text-field label="Descrição*" autocomplete="off"
                                    v-model="departamento.DEPARTAMENTO_NOME"></v-text-field>
                            </v-col>
                            <v-col cols="6">
                                <v-text-field label="Sigla*" autocomplete="off"
                                    v-model="departamento.DEPARTAMENTO_SIGLA"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <v-select label="Hierarquia*" :items="hierarquias" item-text="DESCRICAO"
                                    item-value="COLUNA_ID" hide-details v-model="departamento.HIERARQUIA_ID"></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <v-textarea label="Descrição*"
                                    v-model="departamento.DEPARTAMENTO_DESCRICAO"></v-textarea>
                            </v-col>
                        </v-row>
                        <v-row v-if="departamento.DEPARTAMENTO_ID">
                            <v-col>
                                <v-select label="Ativo*" :items="ativos" :item-value="'id'" :item-text="'text'"
                                    v-model="departamento.DEPARTAMENTO_ATIVO">
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
    name: "MdNovoDepartamento",
    components: { TratarErroAjax },
    data() {
        return {
            msgId: 'msgMdNovoDepartamento',
            msgIdDebug: 'msgMdNovoDepartamentoDebug',
            ativos: [
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
            hierarquias: "DominioModule/getHierarquias",
        }),
        showModal: {
            get() {
                return this.$store.getters['MdNovoDepartamentoModule/getShowModal']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoDepartamentoModule/setShowModal', newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters['MdNovoDepartamentoModule/getFullScreen']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoDepartamentoModule/setFullScreen', newValue)
            }
        },
        departamento: {
            get() {
                return this.$store.getters['MdNovoDepartamentoModule/getDepartamento']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoDepartamentoModule/setDepartamento', newValue)
            }
        }
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.departamento = null
            this.showModal = false
        },
        salvar() {
            this.departamento.DEPARTAMENTO_SIGLA = this.departamento.DEPARTAMENTO_SIGLA ? this.departamento.DEPARTAMENTO_SIGLA.toUpperCase() : null;
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.departamento.DEPARTAMENTO_ID === null ? 'POST' : 'PUT',
                url: this.departamento.DEPARTAMENTO_ID === null ? `${this.baseUrl}/departamento/inserir` : `${this.baseUrl}/departamento/alterar`,
                data: this.departamento
            }).then(r => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success').then(r => {
                    this.$store.dispatch('DepartamentoViewModule/search', this.msgId)
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
