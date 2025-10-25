<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullscreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0">
                        <v-toolbar-title>Detalhes da Tabela</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullscreen = true" v-show="fullscreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullscreen = false" v-show="fullscreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>

                    <perfect-scrollbar>
                        <v-card-text class="mt-5">
                            <v-row>
                                <v-col>
                                    <v-text-field
                                        label="Nome da Tabela"
                                        autocomplete="off"
                                        hide-details
                                        v-model="tabela.DESCRICAO"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </perfect-scrollbar>

                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">
                            salvar
                        </v-btn>
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
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapGetters, mapActions} from "vuex";
import Swal from "sweetalert2";

export default {
name: "MdNovaTabela",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgMdNovaTabela',
            msgIdDebug: 'msgMdNovaTabelaDebug'
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
        }),
        showModal: {
            get() { return this.$store.getters['MdNovaTabelaModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovaTabelaModule/setShowModal', newValue) }
        },
        fullscreen: {
            get() { return this.$store.getters['MdNovaTabelaModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovaTabelaModule/setFullScreen', newValue) }
        },
        tabela: {
            get() { return this.$store.getters['MdNovaTabelaModule/getTabela'] },
            set(newValue) { this.$store.dispatch('MdNovaTabelaModule/setTabela', newValue) }
        }
    },
    methods: {
        ...mapActions({
            setTabelas: 'TabelaGenericaViewModule/setTabelas',
            setColunas: 'TabelaGenericaViewModule/setColunas',
            setTabelaSelecionada: 'TabelaGenericaViewModule/setTabelaSelecionada'
        }),

        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.tabela = null
            this.showModal = null
        },
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.tabela.TABELA_GENERICA_ID === null ? 'POST' : 'PUT',
                url: this.tabela.TABELA_GENERICA_ID === null ? `${this.baseUrl}/tabela_generica/inserir_tabela` : `${this.baseUrl}/tabela_generica/alterar_tabela`,
                data: this.tabela
            })
            .then(r => {
                this.setTabelas(r.data)
                this.setColunas(null)
                this.setTabelaSelecionada(null)
                this.clearFormAndClose()
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
            })
            .catch(e => {
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

<style scoped>

</style>
