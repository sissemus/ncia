<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="900" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0 mb-3">
                        <v-toolbar-title>Detalhes do Perfil</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearForm(false)">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" xs="12" sm="12" md="12" lg="12">
                                <label>Nome</label>
                                <v-text-field
                                    label="Nome"
                                    v-model="perfil['PERFIL_NOME']"
                                    autocomplete="off"
                                    hide-details
                                    solo
                                ></v-text-field>
                            </v-col>
                        </v-row>

                        <v-row class="mb-5">
                            <v-col cols="12" xs="12" sm="12" md="12" lg="12">
                                <v-switch
                                    label="Perfil ativo"
                                    v-model="perfil['PERFIL_ATIVO']"
                                    :true-value="1"
                                    :false-value="0"
                                    hide-details
                                ></v-switch>
                            </v-col>
                        </v-row>
                        <v-divider></v-divider>
                        <v-subheader><b>ACESSOS</b></v-subheader>
                        <v-row>
                            <v-col>
                                <v-treeview
                                    v-model="tree"
                                    :items="aplicacoes"
                                    item-key="APLICACAO_ID"
                                    item-text="APLICACAO_NOME"
                                    open-on-click
                                    selectable
                                    activatable
                                    selected-color="primary"
                                    dense
                                    return-object
                                    @update:active="ativar"
                                    selection-type="independent"
                                >
                                    <template v-slot:prepend="{ item, open }">
                                        <v-icon v-if="!item.file">
                                            {{ item['APLICACAO_ICONE'] }}
                                        </v-icon>
                                        <v-icon v-else>
                                            {{ files[item.file] }}
                                        </v-icon>
                                    </template>
                                </v-treeview>
                            </v-col>
                            <v-divider vertical v-if="aplicacaoSelecionada['APLICACAO_ID'] !== null"></v-divider>
                            <v-col v-if="aplicacaoSelecionada['APLICACAO_ID'] !== null">
                                <v-card class="mx-auto" flat max-width="400">
                                    <v-card-text class="text-center">
                                        <v-icon x-large>
                                            {{ aplicacaoSelecionada['APLICACAO_ICONE'] }}
                                        </v-icon>
                                        <h3 class="headline mb-2">
                                            {{ aplicacaoSelecionada['APLICACAO_NOME'] }}
                                        </h3>
                                        <div class="blue--text mb-2">
                                            {{ aplicacaoSelecionada['APLICACAO_URL'] }}
                                        </div>
                                        <div class="blue--text subheading font-weight-bold">
                                            {{ aplicacaoSelecionada[''] }}
                                        </div>
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-row class="text-left" tag="v-card-text">
                                        <v-col class="text-right mr-4 mb-2" tag="strong" cols="5">
                                            Gestão:
                                        </v-col>
                                        <v-col class="text-center" v-if="aplicacaoSelecionada['APLICACAO_GESTAO'] === 1">
                                            <v-chip x-small color="red" dark>Sim</v-chip>
                                        </v-col>
                                        <v-col class="text-center" v-else>
                                            <v-chip x-small color="green" dark>Não</v-chip>
                                        </v-col>

                                        <v-col class="text-right mr-4 mb-2" tag="strong" cols="5">
                                            Ativa:
                                        </v-col>
                                        <v-col class="text-center" v-if="aplicacaoSelecionada['APLICACAO_ATIVA'] === 1">
                                            <v-chip x-small color="red" dark>Sim</v-chip>
                                        </v-col>
                                        <v-col class="text-center" v-else>
                                            <v-chip x-small color="green" dark>Não</v-chip>
                                        </v-col>

                                        <v-col class="text-right mr-4 mb-2" tag="strong" cols="5">
                                            Ordem:
                                        </v-col>
                                        <v-col class="text-center">
                                            <v-chip x-small>{{ aplicacaoSelecionada['APLICACAO_ORDEM'] }}</v-chip>
                                        </v-col>
                                    </v-row>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">
                            salvar
                        </v-btn>
                        <v-btn color="primary" dark outlined tile @click="clearForm(false)">
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
import {mapActions, mapGetters} from "vuex";
import Aplicacao from "../../store/modules/payloads/Aplicacao.json"
import Swal from "sweetalert2";

export default {
    name: "MdNovoPerfil",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgMdNovoPerfil',
            msgIdDebug: 'msgMdNovoPerfilDebug',
            aplicacaoSelecionada: JSON.parse(JSON.stringify(Aplicacao))
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
            aplicacoes: 'DominioModule/getAplicacoes'
        }),
        showModal: {
            get() {
                return this.$store.getters['MdNovoPerfilModule/getShowModal']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoPerfilModule/setShowModal', newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters['MdNovoPerfilModule/getFullScreen']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoPerfilModule/setFullScreen', newValue)
            }
        },
        perfil: {
            get() {
                return this.$store.getters['MdNovoPerfilModule/getPerfil']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoPerfilModule/setPerfil', newValue)
            }
        },
        aplicacao: {
            get() {
                return this.$store.getters['MdNovoPerfilModule/getAplicacao']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoPerfilModule/setAplicacao', newValue)
            }
        },
        tree: {
            get() { return this.$store.getters['MdNovoPerfilModule/getTree'] },
            set(newValue) { this.$store.dispatch('MdNovoPerfilModule/setTree', newValue) }
        },
        modulo: {
            get() { return this.$store.getters['MdNovoPerfilModule/getModulo'] },
            set(newValue) { this.$store.dispatch('MdNovoPerfilModule/setModulo', newValue) }
        }
    },
    methods: {
        clearForm(showModal = false) {
            this.perfil = null
            this.aplicacao = null
            this.tree = []
            this.aplicacaoSelecionada = JSON.parse(JSON.stringify(Aplicacao))
            this.modulo = null
            this.showModal = showModal
        },
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            let acessos = [];
            this.tree.forEach(r => {
                acessos.push({
                    ACESSO_ID: null,
                    APLICACAO_ID: r['APLICACAO_ID'],
                    PERFIL_ID: this.perfil['PERFIL_ID'],
                    ACESSO_ATIVO: 1,
                })
            })
            this.perfil.acessos = JSON.parse(JSON.stringify(acessos))
            axios({
                method: this.perfil['PERFIL_ID'] === null ? 'POST' : 'PUT',
                url: this.perfil["PERFIL_ID"] === null ? `${this.baseUrl}/perfil/create` : `${this.baseUrl}/perfil/update`,
                data: this.perfil
            }).then(r => {
                if (this.modulo) {
                    this.$store.dispatch(this.modulo).then(() => {
                        this.clearForm(false)
                        Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
                    });
                } else {
                    this.clearForm(false)
                    Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
                }
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        ativar(e) {
            if (e[0] === undefined) {
                this.aplicacaoSelecionada = JSON.parse(JSON.stringify(Aplicacao))
            } else {
                this.aplicacaoSelecionada = JSON.parse(JSON.stringify(e[0]))
            }
        }
    },

}

</script>

<style scoped>

</style>
