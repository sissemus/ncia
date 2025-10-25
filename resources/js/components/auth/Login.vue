<template>
    <v-app id="inspire">
        <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" :color="color" dark flat app prominent style="max-height: 108px; margin-left: 0 !important;">
            <v-row no-gutters>
                <v-col cols="2" style="max-width: 60px">
                    <v-img contain :src="baseUrl + '/img/brasao.png'" alt="Logo" height="100" width="50"></v-img>
                </v-col>
                <v-col cols="10">
                    <div class="d-flex flex-column ml-0 mt-2" style="font-size: 14px">
                        <div class="py-0 my-0">
                            PREFEITURA DE SÃO LUÍS
                        </div>
                        <div class="py-0 my-0">
                            SECRETARIA MUNICIPAL DE SAÚDE - SEMUS
                        </div>
                        <div class="py-0 my-0">
                            SUPERINTENDÊNCIA DE INFORMAÇÃO DA SAÚDE - SIS
                        </div>
                        <div class="py-0 my-0">
                            {{ appName }}
                        </div>
                    </div>
                </v-col>
            </v-row>
        </v-app-bar>

        <v-main>
            <v-container fluid class="fill-height">
                <v-row align="center" justify="center">
                    <v-col cols="12" sm="12" md="5">
                        <v-card class="elevation-2">
                            <v-toolbar :color="color" dark flat>
                                <v-toolbar-title>Identifique-se</v-toolbar-title>
                            </v-toolbar>
                            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                            <div :id="msgIdDebug"></div>
                            <v-card
                                class="d-flex align-center justify-center pa-4 mx-auto"
                                max-width="550"
                                min-height="76"
                                outlined
                                v-if="ambiente === 'homolog'"
                            >
                                <p class="font-weight-black" style="color: red">
                                    AMBIENTE DE HOMOLOGAÇÃO
                                </p>
                            </v-card>
                            <v-card-text>
                                <v-text-field
                                    label="Login"
                                    name="USUARIO_LOGIN"
                                    prepend-icon="mdi-account"
                                    type="text"
                                    v-model="USUARIO_LOGIN"
                                    @keyup.enter="logar"
                                    :color="color"
                                ></v-text-field>

                                <v-text-field
                                    label="Senha"
                                    name="USUARIO_SENHA"
                                    prepend-icon="mdi-lock"
                                    type="password"
                                    v-model="USUARIO_SENHA"
                                    @keyup.enter="logar"
                                    :color="color"
                                ></v-text-field>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn :color="color" outlined tile @click="logar">Acessar</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>

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
        },
        color: {
            type: String,
            required: true
        },
        ambiente: {
            type: String,
            required: true
        },
    },
    data: function () {
        return {
            user: null,
            msgId: 'msgLogin',
            msgIdDebug: 'msgLoginDebug',
            USUARIO_LOGIN: '',
            USUARIO_SENHA: '',
            initiated: false,
        }
    },
    mounted() {
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
