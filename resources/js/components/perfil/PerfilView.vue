<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Perfis</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn fab dark color="primary" small elevation="0" @click="novoPerfil"><v-icon>mdi-plus</v-icon></v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field
                            label="Perfil"
                            autocomplete="off"
                            hide-details
                            v-model="valorPesquisa"
                        ></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="primary" tile @click="limpar">cancelar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="perfis.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left cursor-pointer"  @click="ordenar('PERFIL_ID',form,pesquisar.bind())">
                            Id
                                <v-icon small v-text="order_icon('PERFIL_ID',form)"></v-icon>
                        </th>
                        <th class="text-left cursor-pointer"  @click="ordenar('PERFIL_NOME',form,pesquisar.bind())">
                            Perfil
                                <v-icon small v-text="order_icon('PERFIL_NOME',form)"></v-icon>
                        </th>
                        <th class="text-left">Acessos</th>
                        <th class="text-left">Ativo</th>
                        <th class="text-left">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="perfil in perfis" :key="perfil['PERFIL_ID']">
                        <td>{{ perfil['PERFIL_ID'] }}</td>
                        <td>{{ perfil['PERFIL_NOME'] }}</td>
                        <td>{{ perfil['acessos'].length }}</td>
                        <td>
                            <v-chip color="green" dark x-small v-if="perfil['PERFIL_ATIVO'] === 1">Sim</v-chip>
                            <v-chip color="red" dark x-small v-else>Não</v-chip>
                        </td>
                        <td>
                            <v-row>
                                <v-col>
                                    <v-btn icon @click="selecionar(perfil)" title="Editar">
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
                            color="primary"
                        ></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>

        <MdNovoPerfil></MdNovoPerfil>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";
import MdNovoPerfil from "./MdNovoPerfil";
import TableMixins from '../../mixins/TableMixins';

export default {
name: "PerfilView",
    components: {MdNovoPerfil, TratarErroAjax},
    mixins:[TableMixins],
    props: {
        aplicacoes: {
            type: Array,
            required: true
        }
    },
    mounted() {
        this.$store.dispatch('DominioModule/setAplicacoes', this.aplicacoes)
        this.listar()
    },
    data() {
        return {
            msgId: 'msgPerfilView',
            msgIdDebug: 'msgPerfilViewDebug',
            valorPesquisa: null,
            form:{},
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        perfis: {
            get() { return this.$store.getters['PerfilViewModule/getPerfis'] },
            set(newValue) { this.$store.dispatch('PerfilViewModule/setPerfis', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['PerfilViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('PerfilViewModule/setPagination', newValue) }
        }
    },
    methods: {
        novoPerfil() {
            this.$store.dispatch('MdNovoPerfilModule/setPerfil', null)
            this.$store.dispatch('MdNovoPerfilModule/setModulo', 'PerfilViewModule/listar')
            this.$store.dispatch('MdNovoPerfilModule/setShowModal', true)
        },
        onPageChange() {
            this.listar()
        },
        listar() {
            this.$store.dispatch('PerfilViewModule/listar')
        },
        selecionar(perfil) {
            this.$store.dispatch('MdNovoPerfilModule/setPerfil', perfil)
            this.$store.dispatch('MdNovoPerfilModule/setModulo', 'PerfilViewModule/listar')
            this.$store.dispatch('MdNovoPerfilModule/setShowModal', true)
        },
        limpar() {
            this.valorPesquisa = null
            this.pagination = null
            this.pesquisar()
        },
        pesquisar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/perfil/search`,
                params: {
                    valorPesquisa: this.valorPesquisa,
                    ...this.form,
                    page: this.pagination.current_page
                }
            }).then(r => {
                this.perfis = r.data.data
                this.pagination = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        }
    }
}

</script>

<style scoped>

</style>
