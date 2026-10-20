<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Alterar Senha</v-toolbar-title>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field
                            label="Senha*"
                            type="password"
                            autocomplete="off"
                            v-model="usuario.USUARIO_SENHA"
                        ></v-text-field>
                    </v-col>
                    <v-col>
                        <v-text-field
                            label="Confirmar Senha*"
                            type="password"
                            autocomplete="off"
                            v-model="usuario.USUARIO_SENHA_CONFIRMATION"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-divider class="ma-0"></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark tile @click="salvar"> salvar Senha</v-btn>
            </v-card-actions>

        </v-card>
    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import TratarErroAjax from "../assets/TratarErroAjax";
    import Swal from "sweetalert2";
    export default {
        name: "UsuarioAlterarSenhaView",
        components: {TratarErroAjax},
        data(){
            return {
                msgId: 'msgTabUsuarioAlterarSenha',
                msgIdDebug: 'msgTabUsuarioAlterarSenhaDebug',
            }
        },
        computed: {
            ...mapGetters({
                baseUrl: 'getBaseUrl',
            }),
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
            clearFormAndClose() {
                this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
                this.usuario = null;
            },

            salvar() {
                this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
                axios({
                    method: 'PUT',
                    url: `${this.baseUrl}/usuario/alterar_senha`,
                    data: this.usuario
                })
                    .then(r => {
                        this.usuario = r.data['retorno'];
                        this.clearFormAndClose();
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
