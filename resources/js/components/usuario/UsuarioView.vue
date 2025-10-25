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
                        <v-text-field
                            label="Nome"
                            autocomplete="off"
                            hide-details
                            v-model="usuarioPesquisa.USUARIO_NOME"
                        ></v-text-field>
                    </v-col>
                    <v-col>
                        <v-text-field
                            label="Login"
                            autocomplete="off"
                            hide-details
                            v-model="usuarioPesquisa.USUARIO_LOGIN"
                        ></v-text-field>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="listar">pesquisar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="usuarios.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left cursor-pointer"  @click="ordenar('USUARIO_ID',usuarioPesquisa,listar.bind())">
                            Id
                                <v-icon small v-text="order_icon('USUARIO_ID',usuarioPesquisa)"></v-icon>
                        </th>
                        <th class="text-left cursor-pointer"  @click="ordenar('USUARIO_NOME',usuarioPesquisa,listar.bind())">
                            Nome
                                <v-icon small v-text="order_icon('USUARIO_NOME',usuarioPesquisa)"></v-icon>
                        </th>
                        <th class="text-left cursor-pointer"  @click="ordenar('USUARIO_LOGIN',usuarioPesquisa,listar.bind())">
                            Login
                                <v-icon small v-text="order_icon('USUARIO_LOGIN',usuarioPesquisa)"></v-icon>
                        </th>
                        <th class="text-left cursor-pointer"  @click="ordenar('USUARIO_VIGENCIA',usuarioPesquisa,listar.bind())">
                            Vigência
                                <v-icon small v-text="order_icon('USUARIO_VIGENCIA',usuarioPesquisa)"></v-icon>
                        </th>
                        <th class="text-left">Ativo</th>
                        <th class="text-left">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="usuario in usuarios" :key="usuario['USUARIO_ID']">
                        <td>{{ usuario['USUARIO_ID'] }}</td>
                        <td>{{ usuario['USUARIO_NOME'] }}</td>
                        <td>{{ usuario['USUARIO_LOGIN'] }}</td>
                        <td>{{ usuario['USUARIO_VIGENCIA'] ? formatarDataBR(usuario['USUARIO_VIGENCIA']) : 'Não expira' }}</td>
                        <td>
                            <v-chip x-small v-if="usuario['USUARIO_ATIVO'] === 1" color="green" dark>Sim</v-chip>
                            <v-chip x-small v-else color="red" dark>Não</v-chip>
                        </td>
                        <td>
                            <v-row>
                                <v-col>
                                    <v-btn icon @click="selecionar(usuario)" title="Editar">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </td>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>

            <v-divider></v-divider>
            <v-card-actions>
                <v-row>
                    <v-col>
                        <v-pagination
                            v-show="pagination.total"
                            v-model="pagination.current_page"
                            :length="pagination.last_page"
                            total-visible="10"
                            @input="onPageChange"
                        ></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>

        </v-card>

        <MdNovoUsuario></MdNovoUsuario>
        <MdSelecionarPerfil></MdSelecionarPerfil>

    </div>
</template>

<script>
import {mapGetters} from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoUsuario from "./MdNovoUsuario";
import UtilsMixins from "../../mixins/UtilsMixins";
import MdSelecionarPerfil from "../perfil/MdSelecionarPerfil";
import TableMixins from '../../mixins/TableMixins';

export default {
    name: "UsuarioView",
    components: {MdSelecionarPerfil, MdNovoUsuario, TratarErroAjax},
    mixins: [UtilsMixins,TableMixins],
    data() {
        return {
            msgId: 'msgUsuarioView',
            msgIdDebug: 'msgUsuarioViewDebug',
        }
    },
    mounted() {
        this.listar();
        this.$store.dispatch("UsuarioViewModule/setListar",this.listar.bind(this,1));
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        usuarios: {
            get() { return this.$store.getters['UsuarioViewModule/getUsuarios'] },
            set(newValue) { this.$store.dispatch('UsuarioViewModule/setUsuarios', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['UsuarioViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('UsuarioViewModule/setPagination', newValue) }
        },
        usuarioPesquisa: {
            get() { return this.$store.getters['UsuarioViewModule/getUsuarioPesquisa'] },
            set(newValue) { this.$store.dispatch('UsuarioViewModule/setUsuarioPesquisa', newValue) }
        }
    },
    methods: {
        
        onPageChange() {
            this.listar()
        },

        listar(page = null) {
            if(page) this.pagination.current_page = page;
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
            axios({
                method: "POST",
                url: `${this.baseUrl}/usuario/listar?page=${this.pagination.current_page}`,
                data: {...this.usuarioPesquisa},
            })
                .then((r) => {
                    this.usuarios = r.data["retorno"]["data"];
                    this.pagination = r.data;
                })
                .catch((e) => {
                    console.error("ERRO: ", e);
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response,
                    });
                });
        },

        novo() {
            this.$store.dispatch('MdNovoUsuarioModule/setShowModal', true)
        },

        selecionar(usuario) {
            this.$store.dispatch('MdNovoUsuarioModule/setUsuario', usuario);
            this.$store.dispatch('MdNovoUsuarioModule/setShowModal', true)
        }

    }


}
</script>

<style scoped>

</style>
