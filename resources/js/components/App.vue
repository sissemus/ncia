<template>
    <div id="app">
        <v-app id="inspire">
            <v-navigation-drawer v-model="drawer" :clipped="$vuetify.breakpoint.lgAndUp" app>
                <v-list dense>
                    <v-list-item two-line>
                        <v-list-item-avatar>
                            <v-icon x-large left>mdi-account-circle</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                            <v-list-item-title>{{ usuario['USUARIO_NOME'].toUpperCase() }}</v-list-item-title>

                            <v-list-item-subtitle v-for="(usuarioPerfil, i) in usuario['usuarioPerfis']" :key="i">
                                {{ usuarioPerfil['perfil']['PERFIL_NOME'] }}
                            </v-list-item-subtitle>

                            <v-tooltip bottom v-if="totalUnidades > 0">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-list-item-subtitle
                                        class="unidade-subtitle grey--text text--darken-1 cursor-pointer" v-bind="attrs"
                                        v-on="on">
                                        <v-icon x-small class="mr-1">mdi-map-marker</v-icon>
                                        {{ unidadePrincipal }}
                                        <span v-if="totalUnidades > 1">
                                            + {{ totalUnidades - 1 }}
                                        </span>
                                    </v-list-item-subtitle>
                                </template>

                                <span v-html="unidadesTooltip"></span>
                            </v-tooltip>
                        </v-list-item-content>
                    </v-list-item>

                    <template v-for="(aplicacao, x) in aplicacoes">

                        <!--MENU SEM SUBMENU-->
                        <v-list-item :class="baseUrl + '/' + aplicacao['APLICACAO_URL'] === url ? 'grey lighten-2' : ''"
                            v-if="aplicacao['children'].length === 0" link v-model="aplicacoes[x].model"
                            :href="`${baseUrl}/${aplicacao['APLICACAO_URL']}`">
                            <v-list-item-action>
                                <v-icon>{{ aplicacao['APLICACAO_ICONE'] }}</v-icon>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ aplicacao['APLICACAO_NOME'] }}
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-divider class="my-0"></v-divider>
                        <!--MENU SEM SUBMENU-->

                        <!--MENU COM SUBMENU-->
                        <v-list-group v-if="aplicacao['children'].length > 0" :key="aplicacao['APLICACAO_ID']"
                            v-model="aplicacoes[x].model" :color="color"
                            :prepend-icon="aplicacao['APLICACAO_ICONE'] ? aplicacao['APLICACAO_ICONE'] : 'mdi-menu'"
                            append-icon="mdi-chevron-up">
                            <!--texto do grupo pai-->
                            <template v-slot:activator>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ aplicacao['APLICACAO_NOME'] }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                            <!--texto do grupo pai-->

                            <!--loop nos submenus-->
                            <v-list-item v-for="(child, i) in aplicacao['children']" :key="i" link
                                :href="`${baseUrl}/${child['APLICACAO_URL']}`"
                                :class="baseUrl + '/' + child['APLICACAO_URL'] === url ? 'grey lighten-2' : ''">
                                <v-list-item-action v-if="child['APLICACAO_ICONE']">
                                    <v-icon>{{ child['APLICACAO_ICONE'] }}</v-icon>
                                </v-list-item-action>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ child['APLICACAO_NOME'] }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <!--loop nos submenus-->
                        </v-list-group>
                        <!--MENU COM SUBMENU-->
                    </template>
                </v-list>
            </v-navigation-drawer>

            <form :action="baseUrl + '/logout'" method="post" id="logout">
                <input type="hidden" name="_token" :value="token">
            </form>

            <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" app :color="color" dark>
                <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
                <v-toolbar-title style="width: 300px" class="ml-0 pl-1">
                    <span class="hidden-sm-and-down">{{ appName }}</span>
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-menu :close-on-content-click="false" :nudge-width="200" offset-x>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn x-large icon v-bind="attrs" v-on="on">
                            <v-icon>mdi-account-circle</v-icon>
                        </v-btn>
                    </template>
                    <v-card>
                        <v-list>
                            <v-list-item>
                                <v-list-item-avatar>
                                    <v-icon x-large left>mdi-account-circle</v-icon>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-title>{{ usuario['USUARIO_NOME'] }}</v-list-item-title>
                                    <v-list-item-subtitle v-for="(usuarioPerfil, i) in usuario['usuarioPerfis']"
                                        :key="i">{{
                                            usuarioPerfil['perfil']['PERFIL_NOME'] }}</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                        <v-divider class="my-0"></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn text @click="alterarSenha">alterar senha</v-btn>
                            <v-btn text @click="logout">sair</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-menu>
                <v-avatar tile>
                    <v-img contain :src="baseUrl + '/img/brasao.svg'" alt="Vuetify"></v-img>
                </v-avatar>
            </v-app-bar>

            <v-main>
                <v-container fluid>
                    <slot name="conteudo"></slot>
                </v-container>
            </v-main>

            <block-u-i></block-u-i>
        </v-app>
    </div>
</template>

<script>
import BlockUI from "./assets/BlockUI"
import { mapGetters } from 'vuex'
import UtilsMixins from "../mixins/UtilsMixins";

export default {
    name: "App",
    components: { BlockUI },
    mixins: [UtilsMixins],
    props: {
        source: String,
        color: {
            type: String,
            required: true
        },
        usuario: {
            type: Object,
            required: true
        },
        aplicacoes: {
            type: Array,
            required: true
        },
        avisos: {
            type: Array,
            required: false,
        },
        appName: {
            type: String,
            required: true
        }
    },
    data: () => ({
        dialog: false,
        drawer: null,
        token: '',
    }),
    created() {
        this.expandMenu()
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
            if (error.request.responseType === 'blob' && error.response.data instanceof Blob && error.response.data.type && error.response.data.type.toLowerCase().indexOf('json') !== -1) {
                return new Promise((resolve, reject) => {
                    let reader = new FileReader();
                    reader.onload = () => {
                        error.response.data = JSON.parse(reader.result);
                        resolve(Promise.reject(error));
                    };
                    reader.onerror = () => {
                        reject(error);
                    };
                    reader.readAsText(error.response.data);
                });
            }
            return Promise.reject(error);
        });

        this.token = document.getElementsByTagName("meta").namedItem("csrf-token").getAttribute("content")
    },
    mounted() {
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
        }),
        url() {
            return window.location.href;
        },
        unidades() {
            return this.usuario?.usuarioUnidades || [];
        },

        totalUnidades() {
            return this.unidades.length;
        },

        unidadePrincipal() {
            return this.totalUnidades > 0 && this.unidades[0].unidade
                ? this.unidades[0].unidade.UNIDADE_NOME
                : '';
        },

        unidadesTooltip() {
            let unidades = this.unidades
                .filter(u => u.unidade)
                .map(u => `<li>${u.unidade.UNIDADE_NOME}</li>`)
                .join('');

            return `<ul class="unidades-tooltip-list">${unidades}</ul>`;
        },
    },
    methods: {
        listarAvisos() {
            axios
                .get(`${this.baseUrl}/aviso/listar_avisos_usuario`)
                .then(r => {
                    this.avisos = r.data
                })
                .catch(e => {
                    console.error(e)
                })
        },
        alterarSenha() {
            location.href = this.baseUrl + '/usuario/alteracao_senha'
        },
        logout() {
            document.getElementById('logout').submit()
        },
        uri() {
            return location.href
        },
        inclui(index) {
            return this.aplicacoes[index]['children'].some(r => {
                return (this.baseUrl + '/' + r['APLICACAO_URL']) === window.location.href
            })
        },
        expandMenu() {
            for (let i = 0; i < this.aplicacoes.length; i++) {
                if (this.aplicacoes[i]['children'] === undefined) {
                    if ((this.baseUrl + "/" + this.aplicacoes[i]['APLICACAO_URL']) === location.href) {
                        this.aplicacoes[i].model = true
                        break
                    }
                } else {
                    for (let j = 0; j < this.aplicacoes[i]['children'].length; j++) {
                        if ((this.baseUrl + "/" + this.aplicacoes[i]['children'][j]['APLICACAO_URL']) === location.href) {
                            this.aplicacoes[i].model = true
                            break
                        }
                    }
                }
            }
        }
    }
}
</script>

<style scoped>
*,
*:focus,
*:hover {
    outline: none;
}

.v-data-table>.v-data-table__wrapper>table>tbody>tr>td,
.v-data-table>.v-data-table__wrapper>table>thead>tr>td,
.v-data-table>.v-data-table__wrapper>table>tfoot>tr>td {
    font-size: 13px;
}
</style>

<style>
.cursor-pointer {
    cursor: pointer;
}

.unidades-tooltip-list {
    margin: 0;
    padding-left: 18px;
}

.unidade-subtitle {
    white-space: normal !important;
    word-break: break-word;
    line-height: 1.2;
}
</style>
