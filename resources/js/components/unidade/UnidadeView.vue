<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Unidades</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Nova unidade" fab small elevation="2" color="primary" dark @click="novaUnidade">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Nome da Unidade" autocomplete="off" hide-details
                            v-model="unidadePesquisa.UNIDADE_NOME"></v-text-field>
                    </v-col>
                    <v-col>
                        <v-select label="Unidade Solicitante" :items="solicitantes" item-value="id" item-text="text"
                            clearable hide-details v-model="unidadePesquisa.UNIDADE_SOLICITANTE"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="unidades.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Nome da Unidade</th>
                            <th class="text-left">Solicitante</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="unidade in unidades" :key="unidade['UNIDADE_ID']">
                            <td>{{ unidade['UNIDADE_ID'] }}</td>
                            <td>{{ unidade['UNIDADE_NOME'] }}</td>
                            <td>
                                <v-chip x-small v-if="unidade['UNIDADE_SOLICITANTE'] === 1" color="green"
                                    dark>Sim</v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(unidade)" title="Editar">
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
                            :length="pagination.last_page" total-visible="10" @input="onPageChange"></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>

            <v-divider></v-divider>

            <v-card-actions class="text-center">
                <v-row>
                    <v-col>
                        <v-chip>
                            {{ pagination.total }} registro{{ pagination.total > 1 ? 's' : '' }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>

        <MdNovoUnidade></MdNovoUnidade>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoUnidade from "./MdNovoUnidade";

export default {
    name: "UnidadeView",
    components: { MdNovoUnidade, TratarErroAjax },
    data() {
        return {
            msgId: 'msgUnidadeView',
            msgIdDebug: 'msgUnidadeViewDebug',
            solicitantes: [
                {
                    id: 1,
                    text: 'Sim'
                },
                {
                    id: 0,
                    text: 'Não'
                }
            ]
        }
    },
    mounted() {
        this.search();
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        unidades: {
            get() { return this.$store.getters['UnidadeViewModule/getUnidades'] },
            set(newValue) { this.$store.dispatch('UnidadeViewModule/setUnidades', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['UnidadeViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('UnidadeViewModule/setPagination', newValue) }
        },
        unidadePesquisa: {
            get() { return this.$store.getters['UnidadeViewModule/getUnidadePesquisa'] },
            set(newValue) { this.$store.dispatch('UnidadeViewModule/setUnidadePesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('UnidadeViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.unidadePesquisa = {
                UNIDADE_ID: null,
                UNIDADE_NOME: null,
                UNIDADE_SOLICITANTE: null
            };
            this.pagination.current_page = 1;
            this.search();
        },

        novaUnidade() {
            this.$store.dispatch('MdNovoUnidadeModule/setShowModal', true)
        },

        selecionar(unidade) {
            this.$store.dispatch('MdNovoUnidadeModule/setUnidade', unidade)
            this.$store.dispatch('MdNovoUnidadeModule/setShowModal', true)
        }
    }
}
</script>

<style></style>