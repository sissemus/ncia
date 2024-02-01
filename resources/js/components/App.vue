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
                            <v-list-item-title>{{ usuario['USUARIO_LOGIN'].toUpperCase() }}</v-list-item-title>
<!--                            <v-list-item-subtitle>{{ usuario['perfil'] === null ? '' : usuario['perfil']['PERFIL_NOME'] }}</v-list-item-subtitle>-->
                        </v-list-item-content>
                    </v-list-item>

                    <template v-for="(item, x) in items">
                        <!--MENU SEM SUBMENU-->
                        <v-list-item v-if="!item.children" :key="item.text" link v-model="items[x].model" :href="item.path">
                            <v-list-item-action>
                                <v-icon>{{ item.icon }}</v-icon>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ item.text }}
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-divider class="my-0"></v-divider>
                        <!--MENU SEM SUBMENU-->

                        <!--MENU COM SUBMENU-->
                        <v-list-group
                            v-if="item.children"
                            :key="item.text"
                            v-model="items[x].model"
                            color="primary"
                            :prepend-icon="item.model ? item.icon : item['icon-alt']"
                            append-icon="mdi-chevron-up"
                        >
                            <!--texto do grupo pai-->
                            <template v-slot:activator>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ item.text }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                            <!--texto do grupo pai-->

                            <!--loop nos submenus-->
                            <v-list-item v-for="(child, i) in item.children"
                                         :key="i"
                                         link
                                         :href="child.path"
                                         :class="child.path === url ? 'grey lighten-2' : ''">
                                <v-list-item-action v-if="child.icon">
<!--                                    <v-icon>{{ child.icon }}</v-icon>-->
                                </v-list-item-action>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ child.text }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <!--loop nos submenus-->
                        </v-list-group>
                        <!--MENU COM SUBMENU-->
                    </template>
                </v-list>
            </v-navigation-drawer>

            <form :action="baseUrl+'/logout'" method="post" id="logout">
                <input type="hidden" name="_token" :value="token">
            </form>

<!--            <v-app-bar app color="gray" light extended class="pa-5">-->
<!--                <div class="d-flex align-center">-->
<!--                    <v-img alt="Prefeitura de São Luís" class="shrink mr-2" contain :src="baseUrl+'/img/logo_topo.png'" transition="scale-transition" width="200"/>-->
<!--                </div>-->
<!--                <v-spacer></v-spacer>-->
<!--                <v-divider vertical></v-divider>-->
<!--                <v-btn href="https://github.com/vuetifyjs/vuetify/releases/latest" target="_blank" text>-->
<!--                    <span class="mr-2">FILÔMETRO</span>-->
<!--                    <v-icon>mdi-open-in-new</v-icon>-->
<!--                </v-btn>-->
<!--            </v-app-bar>-->

            <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" app color="gray" light>
                <div class="d-flex align-center">
                    <v-img alt="Prefeitura de São Luís" class="shrink mr-2" contain :src="baseUrl+'/img/logo_topo.png'" transition="scale-transition" width="130"/>
                </div>
                <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
                <v-toolbar-title style="width: 300px" class="ml-0 pl-1">
                    <span class="hidden-sm-and-down">{{ appName }}</span>
                </v-toolbar-title>
<!--                    <v-text-field flat solo-inverted hide-details prepend-inner-icon="mdi-magnify" label="Search" class="hidden-sm-and-down"></v-text-field>-->
                <v-spacer></v-spacer>
                <v-divider vertical></v-divider>

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
                                    <v-list-item-title>{{ usuario['USUARIO_LOGIN'] }}</v-list-item-title>
<!--                                    <v-list-item-subtitle>{{ usuario['perfil'] === null ? '' : usuario['perfil']['PERFIL_NOME'] }}</v-list-item-subtitle>-->
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                        <v-divider class="my-0"></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
<!--                            <v-btn text @click="alterarSenha">alterar senha</v-btn>-->
                            <v-btn text @click="logout">sair</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-menu>
                <v-avatar tile>
                    <v-img contain :src="baseUrl + '/img/brasao.svg'" alt="Brasao" width="50"></v-img>
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
import {mapGetters} from 'vuex'
import UtilsMixins from "../mixins/UtilsMixins";

export default {
    name: "App",
    components: {BlockUI},
    mixins: [UtilsMixins],
    props: {
        source: String,
        menus: Array,
        usuario: {
            type: Object,
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
        items() {
            return this.menus;
        },
        url() {
            return window.location.href;
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
        expandMenu() {
            for (let i = 0; i < this.menus.length; i++) {
                if (this.menus[i].children === undefined) {
                    if (this.menus[i].path === window.location.href) {
                        this.menus[i].model = true
                        break
                    }
                } else {
                    for (let j = 0; j < this.menus[i].children.length; j++) {
                        if (this.menus[i]['children'][j]['path'] === window.location.href) {
                            this.menus[i].model = true
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
    *,*:focus,*:hover{
        outline:none;
    }
    .v-data-table > .v-data-table__wrapper > table > tbody > tr > td, .v-data-table > .v-data-table__wrapper > table > thead > tr > td, .v-data-table > .v-data-table__wrapper > table > tfoot > tr > td {
        font-size: 13px;
    }
</style>
