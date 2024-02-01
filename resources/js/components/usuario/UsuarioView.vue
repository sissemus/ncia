<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Usuários</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn fab small title="Novo usuário" color="green" dark @click="novoUsuario"><v-icon>mdi-plus</v-icon></v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>

            </v-card-text>
            <v-simple-table dense v-show="usuarios.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left">Id</th>
                        <th class="text-left">Login</th>
                        <th class="text-left">Administrador</th>
                        <th class="text-left">Locais</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="usuario in usuarios" :key="usuario['USUARIO_ID']" @click="selecionar(usuario)" style="cursor: pointer">
                        <td>{{ usuario['USUARIO_ID'] }}</td>
                        <td>{{ usuario['USUARIO_LOGIN'] }}</td>
                        <td>
                            <v-chip label x-small v-if="usuario['USUARIO_ADM'] === 1" color="green" dark>Sim</v-chip>
                            <v-chip label x-small v-else color="red" dark>Não</v-chip>
                        </td>
                        <td>
                            <div v-for="(usuarioLocal, j) in usuario['usuarioLocais']" :key="j"><v-icon small color="green">mdi-check-circle</v-icon> {{ usuarioLocal['local']['LOCAL_DESCRICAO'] }}</div>
                        </td>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
        <MdNovoUsuario :locais="locais"></MdNovoUsuario>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";
import MdNovoUsuario from "./MdNovoUsuario";

export default {
name: "UsuarioView",
    components: {MdNovoUsuario, TratarErroAjax},
    props: {
        locais: {
            type: Array
        }
    },
    data() {
        return {
            msgId: 'msgUsuarioView',
            msgIdDebug: 'msgUsuarioViewDebug'
        }
    },
    mounted() {
        this.listar()
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        usuarios: {
            get() { return this.$store.getters['UsuarioViewModule/getUsuarios'] },
            set(newValue) { this.$store.dispatch('UsuarioViewModule/setUsuarios', newValue) }
        }
    },
    methods: {
        listar() {
            this.$store.dispatch('UsuarioViewModule/listar')
        },
        novoUsuario() {
            this.$store.dispatch('MdNovoUsuarioModule/setShowModal', true)
        },
        selecionar(usuario) {
            console.log(usuario)
            this.$store.dispatch('MdNovoUsuarioModule/setModulo', 'UsuarioViewModule/listar')
            this.$store.dispatch('MdNovoUsuarioModule/setUsuario', usuario)
            this.$store.dispatch('MdNovoUsuarioModule/setShowModal', true)
        }
    }
}

</script>

<style scoped>

</style>
