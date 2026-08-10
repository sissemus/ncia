<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes da Equipe</v-toolbar-title>
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
                            <v-col>
                                <v-select label="Ativo*" :items="ativos" :item-value="'id'" :item-text="'text'"
                                    v-model="equipe.EQUIPE_ATIVO">
                                </v-select>
                            </v-col>
                            <v-col>
                                <v-select
                                label="Veículo*"
                                :items="veiculos"
                                item-value="VEICULO_ID"
                                item-text="VEICULO_IDENTIFICACAO"
                                v-model="equipe.VEICULO_ID"
                                ></v-select>
                                
                            </v-col>
                            <v-col>
                                <v-select 
                                    label="Profissional*" 
                                    :items="profissionais" 
                                    item-value="PROFISSIONAL_ID" 
                                    item-text="PROFISSIONAL_NOME"
                                    v-model="equipe.PROFISSIONAL_ID">
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
    name: "MdNovoEquipe",
    components: { TratarErroAjax },
    data() {
        return {
            msgId: 'msgMdNovoEquipe',
            msgIdDebug: 'msgMdNovoEquipeDebug',
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
    mounted() {
        console.log('MdNovoEquipe montado');

        this.$store.dispatch(
            'VeiculoViewModule/search',
            this.msgId
        );

        this.$store.dispatch(
            'ProfissionalViewModule/search',
            this.msgId
        );
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getShowModal']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setShowModal', newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getFullScreen']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setFullScreen', newValue)
            }
        },
        equipe: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getEquipe']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setEquipe', newValue)
            }
        },
        veiculos() {
            return this.$store.getters['VeiculoViewModule/getVeiculos']
        },
        profissionais() {
            return this.$store.getters['ProfissionalViewModule/getProfissionais']
        },
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.equipe = null
            this.showModal = false
        },
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.equipe.EQUIPE_ID === null ? 'POST' : 'PUT',
                url: this.equipe.EQUIPE_ID === null ? `${this.baseUrl}/equipe/inserir` : `${this.baseUrl}/equipe/alterar`,
                data: this.equipe
            }).then(r => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success').then(r => {
                    this.$store.dispatch('EquipeViewModule/search', this.msgId)
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
