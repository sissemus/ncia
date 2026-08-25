<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Usuários</v-toolbar-title>

                <v-spacer></v-spacer>

                <v-btn title="Novo usuário" fab small elevation="2" color="primary" dark @click="novo">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Nome" autocomplete="off" hide-details
                            v-model="usuarioPesquisa.USUARIO_NOME"></v-text-field>
                    </v-col>

                    <v-col>
                        <v-text-field label="Login" autocomplete="off" hide-details
                            v-model="usuarioPesquisa.USUARIO_LOGIN"></v-text-field>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="usuarios.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left cursor-pointer"
                                @click="ordenar('USUARIO_ID')">
                                Id
                                <v-icon small v-text="orderIcon('USUARIO_ID')"></v-icon>
                            </th>

                            <th class="text-left cursor-pointer"
                                @click="ordenar('USUARIO_NOME')">
                                Nome
                                <v-icon small v-text="orderIcon('USUARIO_NOME')"></v-icon>
                            </th>

                            <th class="text-left cursor-pointer"
                                @click="ordenar('USUARIO_LOGIN')">
                                Login
                                <v-icon small v-text="orderIcon('USUARIO_LOGIN')"></v-icon>
                            </th>

                            <th class="text-left">Perfil</th>
                            <th class="text-left">Unidades</th>

                            <th class="text-left cursor-pointer"
                                @click="ordenar('USUARIO_VIGENCIA')">
                                Vigência
                                <v-icon small v-text="orderIcon('USUARIO_VIGENCIA')"></v-icon>
                            </th>

                            <th class="text-left">Ativo</th>
                            <th class="text-left">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="usuario in usuarios" :key="usuario.USUARIO_ID">
                            <td>{{ usuario.USUARIO_ID }}</td>
                            <td>{{ usuario.USUARIO_NOME }}</td>
                            <td>{{ usuario.USUARIO_LOGIN }}</td>

                            <td>
                                <template v-if="usuario.usuarioPerfis && usuario.usuarioPerfis.length">
                                    <v-chip v-for="usuarioPerfil in usuario.usuarioPerfis"
                                        :key="usuarioPerfil.USUARIO_PERFIL_ID" x-small class="mr-1 mb-1">
                                        {{ nomePerfil(usuarioPerfil) }}
                                    </v-chip>
                                </template>
                                <span v-else>-</span>
                            </td>

                            <td>
                                <template v-if="usuario.usuarioUnidades && usuario.usuarioUnidades.length">
                                    <v-chip v-for="usuarioUnidade in usuario.usuarioUnidades"
                                        :key="usuarioUnidade.USUARIO_UNIDADE_ID" x-small class="mr-1 mb-1">
                                        {{ usuarioUnidade.unidade ? usuarioUnidade.unidade.UNIDADE_NOME : "-" }}
                                    </v-chip>
                                </template>
                                <span v-else>-</span>
                            </td>

                            <td>{{ usuario.USUARIO_VIGENCIA ? formatarDataBR(usuario.USUARIO_VIGENCIA) : "Não expira" }}</td>

                            <td>
                                <v-chip x-small v-if="usuario.USUARIO_ATIVO === 1" color="green" dark>Sim</v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>

                            <td>
                                <v-btn icon @click="selecionar(usuario)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </v-simple-table>

            <v-divider></v-divider>

            <v-card-actions>
                <v-row>
                    <v-col>
                        <v-pagination v-show="pagination.total" v-model="pagination.current_page"
                            :length="pagination.last_page" total-visible="10"
                            @input="onPageChange"></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>

            <v-divider></v-divider>

            <v-card-actions class="text-center">
                <v-row>
                    <v-col>
                        <v-chip>
                            {{ pagination.total }} registro{{ pagination.total !== 1 ? "s" : "" }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
        <MdSelecionarPerfil></MdSelecionarPerfil>

        <MdNovoUsuario></MdNovoUsuario>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoUsuario from "./MdNovoUsuario";
import MdSelecionarPerfil from "../perfil/MdSelecionarPerfil";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "UsuarioView",
    components: { TratarErroAjax, MdNovoUsuario, MdSelecionarPerfil },
    mixins: [UtilsMixins],

    data() {
        return {
            msgId: "msgUsuarioView",
            msgIdDebug: "msgUsuarioViewDebug",
            usuarios: [],
            usuarioPesquisa: {
                USUARIO_NOME: null,
                USUARIO_LOGIN: null,
                order_by: "USUARIO_ID",
                order_direction: "asc"
            },
            pagination: {
                current_page: 1,
                total: 0,
                last_page: 0
            }
        }
    },

    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        })
    },

    mounted() {
        this.$store.dispatch("UsuarioViewModule/setListar", this.listar);
        this.listar();
    },

    methods: {
        listar() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            let params = JSON.parse(JSON.stringify(this.usuarioPesquisa));
            params.page = this.pagination.current_page;

            axios.get(`${this.baseUrl}/usuario/listar`, { params })
                .then(r => {
                    let retorno = r.data.retorno;

                    if (retorno && Array.isArray(retorno.data)) {
                        this.usuarios = retorno.data;
                        this.pagination.current_page = retorno.current_page || 1;
                        this.pagination.total = retorno.total || 0;
                        this.pagination.last_page = retorno.last_page || 0;
                        return;
                    }

                    this.usuarios = Array.isArray(retorno) ? retorno : [];
                    this.pagination.current_page = 1;
                    this.pagination.total = this.usuarios.length;
                    this.pagination.last_page = this.usuarios.length ? 1 : 0;
                })
                .catch(e => {
                    console.error("ERRO: ", e);

                    if (e.response) {
                        this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                            id: this.msgId,
                            response: e.response
                        });
                    }
                });
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.listar();
        },

        onPageChange() {
            this.listar();
        },

        ordenar(campo) {
            if (this.usuarioPesquisa.order_by === campo) {
                this.usuarioPesquisa.order_direction = this.usuarioPesquisa.order_direction === "asc" ? "desc" : "asc";
            } else {
                this.usuarioPesquisa.order_by = campo;
                this.usuarioPesquisa.order_direction = "asc";
            }

            this.pagination.current_page = 1;
            this.listar();
        },

        orderIcon(campo) {
            if (this.usuarioPesquisa.order_by !== campo)
                return "mdi-unfold-more-horizontal";

            return this.usuarioPesquisa.order_direction === "asc" ? "mdi-arrow-up" : "mdi-arrow-down";
        },

        nomePerfil(usuarioPerfil) {
            if (!usuarioPerfil || !usuarioPerfil.perfil)
                return "-";

            return usuarioPerfil.perfil.PERFIL_NOME || usuarioPerfil.perfil.PERFIL_DESCRICAO || "-";
        },

        novo() {
            this.$store.dispatch("MdNovoUsuarioModule/setUsuario", null);
            this.$store.dispatch("MdNovoUsuarioModule/setShowModal", true);
        },

        selecionar(usuario) {
            this.$store.dispatch("MdNovoUsuarioModule/setUsuario", usuario);
            this.$store.dispatch("MdNovoUsuarioModule/setShowModal", true);
        }
    }
}
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
