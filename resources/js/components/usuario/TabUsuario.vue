<template>
    <div>
        <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
        <div :id="msgIdDebug"></div>

        <v-card-text class="mt-5">

            <v-row>
                <v-col cols="md-7">
                    <v-text-field
                        label="Nome*"
                        autocomplete="off"
                        v-model="usuario.USUARIO_NOME"
                    ></v-text-field>
                </v-col>
                <v-col cols="md-5">
                    <v-text-field
                        label="CPF*"
                        v-mask="'###.###.###-##'"
                        autocomplete="off"
                        v-model="usuario.USUARIO_DOC"
                        :error-messages="errors.USUARIO_DOC"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="md-6">
                    <v-text-field
                        label="Login*"
                        autocomplete="off"
                        v-model="usuario.USUARIO_LOGIN"
                    ></v-text-field>
                </v-col>
                <v-col cols="md-6">
                    <v-text-field
                        label="E-mail*"
                        type="email"
                        autocomplete="off"
                        v-model="usuario.USUARIO_EMAIL"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="md-6">
                    <v-text-field
                        label="Senha*"
                        type="password"
                        autocomplete="off"
                        v-model="senha"
                    >
                    </v-text-field>

                </v-col>

                <v-col cols="md-6">
                    <v-text-field
                        label="Confirmar Senha*"
                        type="password"
                        autocomplete="off"
                        v-model="confirmarSenha"
                    ></v-text-field>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="md-6">
                    <v-text-field
                        label="Vigência"
                        type="date"
                        autocomplete="off"
                        v-model="usuario.USUARIO_VIGENCIA"
                    ></v-text-field>
                </v-col>
                <v-col cols="md-6">
                    <v-select
                        label="Ativo*"
                        :items="ativos"
                        :item-value="'id'"
                        :item-text="'text'"
                        v-model="usuario.USUARIO_ATIVO"
                    ></v-select>
                </v-col>
            </v-row>

        </v-card-text>

        <v-divider class="ma-0"></v-divider>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" dark tile @click="salvar"> salvar Usuário</v-btn>
        </v-card-actions>

    </div>
</template>

<script>
import {mapGetters} from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "TabUsuario",
    components: {TratarErroAjax},
    mixins: [UtilsMixins],
    data() {
        return {
            msgId: 'msgTabUsuario',
            msgIdDebug: 'msgTabUsuarioDebug',
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
            senha: null,
            confirmarSenha: null,
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
            listar: 'UsuarioViewModule/getListar',
        }),
        pessoa: {
            get() {
                return this.$store.getters["MdNovoUsuarioModule/getPessoa"];
            },
            set(newValue) {
                this.$store.dispatch("MdNovoUsuarioModule/setPessoa", newValue);
            },
        },
        usuario: {
            get() {
                return this.$store.getters['MdNovoUsuarioModule/getUsuario']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoUsuarioModule/setUsuario', newValue)
            }
        },
    },
    methods: {

        selecionarPessoa() {
            this.$store.dispatch(
                "MdSelecionarPessoaModule/setQuemChamou",
                "TabUsuario"
            );
            this.$store.dispatch("MdSelecionarPessoaModule/setShowModal", true);
        },
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            this.usuario = null;
            this.detalheEscalaItem = null;
            this.showModal = false;
            this.senha = null;
            this.confirmarSenha = null;
        },
        salvar() {
            this.usuario.USUARIO_DOC = this.usuario.USUARIO_DOC.replace(/\D/g, '');
            this.usuario.USUARIO_SENHA = this.senha;
            this.usuario.USUARIO_SENHA_CONFIRMATION = this.confirmarSenha;
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);

            console.log('usuario: ', this.usuario);

            axios({
                method: this.usuario.USUARIO_ID === null ? 'POST' : 'PUT',
                url: this.usuario.USUARIO_ID === null ? `${this.baseUrl}/usuario/inserir` : `${this.baseUrl}/usuario/alterar`,
                data: this.usuario
            })
                .then(r => {
                    this.listar();
                    this.usuario = r.data['retorno'];
                    Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
                })
                .catch(e => {
                    console.error('ERRO: ', e);
                    this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                        id: this.msgId,
                        response: e.response
                    })
                })

        },
    }
}
</script>

<style scoped>

</style>
