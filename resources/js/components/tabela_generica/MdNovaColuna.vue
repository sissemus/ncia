<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="1000" scrollable :fullscreen="fullscreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0">
                        <v-toolbar-title>Detalhes da Coluna</v-toolbar-title>
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
                                    <v-select
                                        label="Tabela"
                                        hide-details
                                        :items="tabelas"
                                        item-text="DESCRICAO"
                                        item-value="TABELA_ID"
                                        return-object
                                        v-model="coluna.tabela"
                                        @change="onChangeTabela"
                                    ></v-select>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col>
                                    <v-text-field
                                        label="DESCRICAO"
                                        hide-details
                                        autocomplete="off"
                                        v-model="coluna.DESCRICAO"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col>
                                    <v-select
                                        label="Ativo"
                                        :items="ativo"
                                        item-value="id"
                                        item-text="text"
                                        v-model="coluna.ATIVO"
                                    ></v-select>
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
    name: "MdNovaColuna",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgMdNovaColuna',
            msgIdDebug: 'msgMdNovaColunaDebug',
            ativo: [
                {id: 1, text: 'Sim'},
                {id: 0, text: 'Não'},
            ],
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
            tabelas: 'TabelaGenericaViewModule/getTabelas'
        }),
        showModal: {
            get() { return this.$store.getters['MdNovaColunaModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovaColunaModule/setShowModal', newValue) }
        },
        fullscreen: {
            get() { return this.$store.getters['MdNovaColunaModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovaColunaModule/setFullScreen', newValue) }
        },
        coluna: {
            get() { return this.$store.getters['MdNovaColunaModule/getColuna'] },
            set(newValue) { this.$store.dispatch('MdNovaColunaModule/setColuna', newValue) }
        },
    },
    methods: {
        ...mapActions({
            setColunas: 'TabelaGenericaViewModule/setColunas',
            setTabelaSelecionada: 'TabelaGenericaViewModule/setTabelaSelecionada'
        }),
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.coluna = null
            this.showModal = false
        },
        onChangeTabela() {
            this.coluna.TABELA_ID = this.coluna.tabela.TABELA_ID
        },
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.coluna.TABELA_GENERICA_ID === null ? 'POST' : 'PUT',
                url: this.coluna.TABELA_GENERICA_ID === null ? `${this.baseUrl}/tabela_generica/inserir_coluna` : `${this.baseUrl}/tabela_generica/alterar_coluna`,
                data: this.coluna
            })
            .then(r => {
                this.setTabelaSelecionada(this.coluna.tabela)
                this.setColunas(r.data)
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
