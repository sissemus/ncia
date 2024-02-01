<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="700" scrollable>
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0 mb-3">
                        <v-toolbar-title>Novo Usuário</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="clearForm(false)">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>
                    <v-card-text>
                        <v-row>
                            <v-col>
                                <label>Nome</label>
                                <v-text-field
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    v-model="usuario['USUARIO_NOME']"
                                ></v-text-field>
                            </v-col>
                            <v-col>
                                <label>Login</label>
                                <v-text-field
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    v-model="usuario['USUARIO_LOGIN']"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <label>CPF</label>
                                <v-text-field
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    v-model="usuario['USUARIO_CPF']"
                                ></v-text-field>
                            </v-col>
                            <v-col>
                                <label>Senha</label>
                                <v-text-field
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    type="password"
                                    v-model="usuario['USUARIO_SENHA']"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <label>Ativo</label>
                                <v-select
                                    :items="simNao"
                                    item-value="id"
                                    item-text="text"
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    v-model="usuario['USUARIO_ATIVO']"
                                ></v-select>
                            </v-col>
                            <v-col>
                                <label>Administrador</label>
                                <v-select
                                    :items="simNao"
                                    item-value="id"
                                    item-text="text"
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    v-model="usuario['USUARIO_ADM']"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <label>Local</label>
                                <v-select
                                    hide-details
                                    solo
                                    autocomplete="off"
                                    :items="locais"
                                    item-text="LOCAL_DESCRICAO"
                                    item-value="LOCAL_ID"
                                    return-object
                                    v-model="localSelecionado"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="text-right">
                                <v-btn color="primary" @click="addUsuarioLocal">adicionar local</v-btn>
                            </v-col>
                        </v-row>
                        <v-simple-table dense v-show="usuario['usuarioLocais'].length" class="mb-0">
                            <template v-slot:default>
                                <thead>
                                <tr>
                                    <th class="text-left">Id</th>
                                    <th class="text-left">Local</th>
                                    <th class="text-left">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(usuarioLocal, i) in usuario['usuarioLocais']" :key="i">
                                    <td>{{ usuarioLocal['USUARIO_LOCAL_ID'] }}</td>
                                    <td>{{ usuarioLocal['local']['LOCAL_DESCRICAO'] }}</td>
                                    <td><v-btn icon small @click="confirmarExclusao(i)"><v-icon>mdi-delete</v-icon></v-btn></td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark @click="salvar">
                            salvar
                        </v-btn>
                        <v-btn color="primary" dark outlined @click="clearForm(false)">
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
import Local from "../../store/modules/payloads/Local.json"
import Swal from "sweetalert2";

export default {
name: "MdNovoUsuario",
    components: {TratarErroAjax},
    props: {
        locais: {
            type: Array
        }
    },
    data() {
        return {
            msgId: 'msgMdNovoUsuario',
            msgIdDebug: 'msgMdNovoUsuarioDebug',
            simNao: [
                {id: 0, text: 'Não'},
                {id: 1, text: 'Sim'},
            ],
            localSelecionado: JSON.parse(JSON.stringify(Local))
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setShowModal', newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setFullScreen', newValue) }
        },
        usuario: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getUsuario'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setUsuario', newValue) }
        },
        modulo: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getModulo'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setModulo', newValue) }
        }
    },
    methods: {
        clearForm(showModal = false) {
            this.usuario = null
            this.localSelecionado = JSON.parse(JSON.stringify(Local))
            this.showModal = showModal
        },
        salvar() {
            let data = JSON.parse(JSON.stringify(this.usuario))
            console.log(data)
            data['usuarioLocais'].forEach(r => {
                delete r['local']
            })
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.usuario['USUARIO_ID'] === null ? 'POST' : 'PUT',
                url: this.usuario['USUARIO_ID'] === null ? `${this.baseUrl}/usuario/create` : `${this.baseUrl}/usuario/update`,
                data
            }).then(r => {
                if (this.modulo) {
                    this.$store.dispatch(this.modulo)
                }
                this.clearForm(false)
                Swal.fire("Sucesso", "Salvo com sucesso", "success")
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        addUsuarioLocal() {
            try {
                this.$store.dispatch('MdNovoUsuarioModule/addUsuarioLocal', this.localSelecionado)
                this.localSelecionado = JSON.parse(JSON.stringify(Local))
            } catch (e) {
                Swal.fire("Erro", e, "error")
            }
        },
        confirmarExclusao(i) {
            Swal.fire({
                title: 'Confirmação',
                text: "Você confirma a remoção?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#007bff',
                confirmButtonText: 'Sim',
                cancelButtonText: 'Não'
            }).then((result) => {
                if (result.value) {
                    this.excluir(i)
                }
            })
        },
        excluir(i) {
            this.$store.dispatch('MdNovoUsuarioModule/spliceUsuarioLocal', i)
        }
    }
}

</script>

<style scoped>

</style>
