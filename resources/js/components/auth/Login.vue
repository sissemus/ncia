<template>
    <v-app id="inspire">
        <v-app-bar app color="gray" light extended class="pa-5">
            <v-container>
                <v-layout row>
                    <h2 style="color: #2071B7"><b>FILÔMETRO</b></h2>
                </v-layout>
                <v-layout row>
                    <h5 style="color: #2071B7"><b>CONSULTA DE PONTOS DE VACINAÇÃO</b></h5>
                </v-layout>
            </v-container>

            <v-spacer></v-spacer>
            <div class="d-flex align-center">
                <v-img alt="Prefeitura de São Luís" class="shrink mr-0" contain :src="baseUrl+'/img/logo_topo.png'" transition="scale-transition" width="150"/>
            </div>
        </v-app-bar>

        <v-main style="background-color: #eee">
            <v-container fluid class="fill-height">
                <v-row align="center" justify="center">
                    <v-col cols="12" sm="12" md="5">
                        <v-card class="elevation-2">
                            <v-toolbar color="gray" light elevation="1">
                                <v-toolbar-title>Identifique-se</v-toolbar-title>
                            </v-toolbar>
                            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                            <div :id="msgIdDebug"></div>
                            <v-card-text>
                                <v-text-field
                                    label="Login"
                                    name="USUARIO_LOGIN"
                                    prepend-icon="mdi-account"
                                    type="text"
                                    v-model="USUARIO_LOGIN"
                                    solo
                                    @keyup.enter="logar"
                                ></v-text-field>

                                <v-text-field
                                    label="Senha"
                                    name="USUARIO_SENHA"
                                    prepend-icon="mdi-lock"
                                    type="password"
                                    v-model="USUARIO_SENHA"
                                    solo
                                    @keyup.enter="logar"
                                ></v-text-field>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn x-large color="green darken-2" block dark @click="logar">Acessar</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
        <v-footer light padless>
            <v-card flat tile class="text-center" style="width: 100%">
                <v-card-text>
                    <v-btn class="mx-4" icon href="https://twitter.com/prefeiturasl" target="_blank">
                        <v-icon size="24px">
                            mdi-twitter
                        </v-icon>
                    </v-btn>
                    <v-btn class="mx-4" icon href="https://www.facebook.com/PrefeituraDeSaoLuis" target="_blank">
                        <v-icon size="24px">
                            mdi-facebook
                        </v-icon>
                    </v-btn>
                    <v-btn class="mx-4" icon href="https://www.instagram.com/prefeiturasaoluis/" target="_blank">
                        <v-icon size="24px">
                            mdi-instagram
                        </v-icon>
                    </v-btn>
                </v-card-text>

                <v-card-text class="pt-0">
                    PREFEITURA DE SÃO LUÍS<br>
                    Av. Pedro II, S/N° - Palácio De La Ravardière - Centro - São Luís - MA - CEP: 65010-904
                </v-card-text>

                <v-card-text>
                    {{ new Date().getFullYear() }} — SEMIT
                </v-card-text>
                <v-img :src="baseUrl+'/img/predios3.png'"></v-img>
            </v-card>
        </v-footer>

        <block-u-i></block-u-i>
    </v-app>
</template>

<script>
import {mapGetters, mapActions} from 'vuex'
import TratarErroAjax from "../assets/TratarErroAjax";
import BlockUI from "../assets/BlockUI";
import Swal from 'sweetalert2';

export default {
    name: "App",
    components: {BlockUI, TratarErroAjax},
    props: {
        appName: {
            type: String,
            required: true
        }
    },
    data: function () {
        return {
            user: null,
            msgId: 'msgLogin',
            msgIdDebug: 'msgLoginDebug',
            USUARIO_LOGIN: '',
            USUARIO_SENHA: '',
            initiated: false,
            icons: [
                'mdi-facebook',
                'mdi-twitter',
                'mdi-linkedin',
                'mdi-instagram',
            ],
        }
    },
    mounted() {
        console.log(this.baseUrl)
        console.log('appName', this.appName)
    },
    created() {
        axios.interceptors.request.use((config) => {
            this.$store.commit('setOverlay', true);
            return config;
        }, (error) => {
            this.$store.commit('setOverlay', false);
            return Promise.reject(error);
        });

        axios.interceptors.response.use((response) => {
            this.$store.commit('setOverlay', false);
            return response;
        }, (error) => {
            this.$store.commit('setOverlay', false);
            return Promise.reject(error);
        });
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        })
    },
    methods: {
        ...mapActions({
            tratarErro: 'TratarErroAjaxModule/tratarErro'
        }),
        logar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            let data = {
                USUARIO_LOGIN: this.USUARIO_LOGIN || null,
                USUARIO_SENHA: this.USUARIO_SENHA || null,
                _token: document
                    .getElementsByTagName("meta")
                    .namedItem("csrf-token")
                    .getAttribute("content")
            }
            if (data.usuarioLogin === null) {
                Swal.fire('Erro', 'Informe o login', 'error')
            } else if (data.usuarioSenha === null) {
                Swal.fire('Erro', 'Informe a senha', 'error')
            } else {
                axios.post(`${this.baseUrl}/login`, data)
                    .then(response => {
                        window.location.href = this.baseUrl + '/home'
                    })
                    .catch(err => {
                        console.error('ERRO: ', err)
                        this.tratarErro({
                            id: this.msgId,
                            response: err.response
                        })
                    })
            }
        },
    }
}
</script>

<style scoped>
*,*:focus,*:hover{
    outline:none;
}
</style>
