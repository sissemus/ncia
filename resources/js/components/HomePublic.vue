<template>=
    <v-app id="inspire">
        <!--        <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" color="primary" dark flat app prominent style="max-height: 108px; margin-left: 0 !important;">-->
        <!--            <v-row no-gutters>-->
        <!--                <v-col cols="2" style="max-width: 60px">-->
        <!--                    <v-img contain :src="baseUrl + '/img/brasao.png'" alt="Logo" height="100" width="50"></v-img>-->
        <!--                </v-col>-->
        <!--                <v-col cols="10">-->
        <!--                    <div class="d-flex flex-column ml-0 mt-2" style="font-size: 14px">-->
        <!--                        <div class="py-0 my-0">-->
        <!--                            PREFEITURA DE SÃO LUÍS-->
        <!--                        </div>-->
        <!--                        <div class="py-0 my-0">-->
        <!--                            SECRETARIA MUNICIPAL DE SAÚDE - SEMUS-->
        <!--                        </div>-->
        <!--                        <div class="py-0 my-0">-->
        <!--                            SUPERINTENDÊNCIA DE INFORMAÇÃO DA SAÚDE - SIS-->
        <!--                        </div>-->
        <!--                        <div class="py-0 my-0">-->
        <!--                            {{ appName }}-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </v-col>-->
        <!--            </v-row>-->
        <!--        </v-app-bar>-->
        <v-app-bar app color="gray" light extended class="pa-5">
            <div class="d-flex align-center">
                <v-img alt="Prefeitura de São Luís" class="shrink mr-2" contain :src="baseUrl+'/img/logo_topo.png'" transition="scale-transition" width="200"/>
            </div>
            <v-spacer></v-spacer>
            <v-divider vertical></v-divider>
            <v-btn href="https://github.com/vuetifyjs/vuetify/releases/latest" target="_blank" text>
                <span class="mr-2">PROJETO BASE</span>
                <v-icon>mdi-open-in-new</v-icon>
            </v-btn>
        </v-app-bar>

        <v-main>
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
        <v-footer dark padless>
            <v-card flat tile class="lighten-1 white--text text-center" color="#2071B7">
                <v-card-text>
                    <v-btn v-for="icon in icons" :key="icon" class="mx-4 white--text" icon>
                        <v-icon size="24px">
                            {{ icon }}
                        </v-icon>
                    </v-btn>
                </v-card-text>

                <v-card-text class="white--text pt-0">
                    Phasellus feugiat arcu sapien, et iaculis ipsum elementum sit amet. Mauris cursus commodo interdum. Praesent ut risus eget metus luctus accumsan id ultrices nunc. Sed at orci sed massa consectetur dignissim a sit amet dui. Duis commodo vitae velit et faucibus. Morbi vehicula lacinia malesuada. Nulla placerat augue vel ipsum ultrices, cursus iaculis dui sollicitudin. Vestibulum eu ipsum vel diam elementum tempor vel ut orci. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
                </v-card-text>

                <v-divider></v-divider>

                <v-card-text class="white--text">
                    {{ new Date().getFullYear() }} — <strong>SEMUS</strong>
                </v-card-text>
            </v-card>
        </v-footer>

        <block-u-i></block-u-i>
    </v-app>
</template>

<script>
import {mapGetters} from "vuex";
import BlockUI from "./assets/BlockUI";

export default {
    name: 'HomePublic',
    components: {BlockUI},
    data() {
        return {
            icons: [
                'mdi-facebook',
                'mdi-twitter',
                'mdi-linkedin',
                'mdi-instagram',
            ],
        }
    },
    mounted() {

    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        })
    },
    methods: {

    }
}
</script>
