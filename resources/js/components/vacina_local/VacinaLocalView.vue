<template>
    <div>
        <v-card>
            <div>
                <v-toolbar class="elevation-1">
                    <v-icon class="mr-1">mdi-folder-plus</v-icon>
                    <v-toolbar-title>Entradas de Vacinas</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <div :id="msgIdDebug"></div>
                <v-card-text>
                    <v-row>
                        <v-col>
                            <v-select
                                label="Local de vacinação"
                                solo
                                hide-details
                                prepend-icon="mdi-map-marker-radius"
                                :items="usuarioLocais"
                                item-value="LOCAL_ID"
                                v-model="vacinaLocal['LOCAL_ID']"
                            >
                                <template v-slot:item="{ item, attrs, on }">
                                    {{ item['local']['LOCAL_ID'] }} - {{ item['local']['LOCAL_DESCRICAO'] }}
                                </template>
                                <template v-slot:selection="{ item, attrs, on }">
                                    {{ item['local']['LOCAL_ID'] }} - {{ item['local']['LOCAL_DESCRICAO'] }}
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <v-select
                                label="Vacina"
                                solo
                                hide-details
                                prepend-icon="mdi-needle"
                                :items="vacinas"
                                item-value="VACINA_ID"
                                v-model="vacinaLocal['VACINA_ID']"
                            >
                                <template v-slot:item="{ item, attrs, on }">
                                    {{ item['VACINA_ID'] }} - {{ item['VACINA_NOME'] }}
                                </template>
                                <template v-slot:selection="{ item, attrs, on }">
                                    {{ item['VACINA_ID'] }} - {{ item['VACINA_NOME'] }}
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <v-text-field
                                label="Quantidade"
                                solo
                                hide-details
                                prepend-icon="mdi-chart-box"
                                v-model="vacinaLocal['VACINA_LOCAL_QTD']"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" dark outlined tile @click="save">
                        SALVAR
                    </v-btn>
                </v-card-actions>
            </div>
        </v-card>

        <v-card>
            <v-card-text>
                <v-simple-table  v-show="vacinasLocais.length" class="mb-0" dense="">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left">Data</th>
                            <th class="text-left">Local</th>
                            <th class="text-left">Vacina</th>
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="vacinaLocal in vacinasLocais" :key="vacinaLocal['clienteId']">
                            <td>{{ formatarDataHora(vacinaLocal['VACINA_LOCAL_DH_CADASTRO']) }}</td>
                            <td>{{ vacinaLocal['local']['LOCAL_DESCRICAO'] }}</td>
                            <td>{{ vacinaLocal['vacina']['VACINA_NOME'] }}</td>
                            <td>{{ vacinaLocal['VACINA_LOCAL_QTD'] }}</td>
                            <td>
                                <v-btn icon @click="select(vacinaLocal)"><v-icon>mdi-pencil</v-icon></v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card-text>
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
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapGetters} from "vuex";
import VacinaLocal from "../../store/modules/payloads/VacinaLocal.json";
import Swal from "sweetalert2";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
name: "VacinaLocalView",
    components: {TratarErroAjax},
    mixins: [UtilsMixins],
    props: {
        usuarioLocais: {
            type: Array,
        },
        vacinas: {
            type: Array,
        },
    },
    mounted() {
        this.search()
    },
    data() {
        return {
            msgId: 'msgVacinaLocalView',
            msgIdDebug: 'msgVacinaLocalViewDebug',
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        vacinasLocais: {
            get() { return this.$store.getters['VacinaLocalViewModule/getVacinasLocais'] },
            set(newValue) { this.$store.dispatch('VacinaLocalViewModule/setVacinasLocais', newValue) }
        },
        vacinaLocal: {
            get() { return this.$store.getters['VacinaLocalViewModule/getVacinaLocal'] },
            set(newValue) { this.$store.dispatch('VacinaLocalViewModule/setVacinaLocal', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['VacinaLocalViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('VacinaLocalViewModule/setPagination', newValue) }
        }
    },
    methods: {
        onPageChange() {
            this.search()
        },
        save() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.vacinaLocal['VACINA_LOCAL_ID'] === null ? 'POST' : 'PUT',
                url: this.vacinaLocal['VACINA_LOCAL_ID'] === null ? `${this.baseUrl}/vacina-local/create` : `${this.baseUrl}/vacina-local/update`,
                data: this.vacinaLocal
            }).then(r => {
                this.vacinaLocal = null
                Swal.fire("Sucesso", "Salvo com sucesso", "success").then(() => {
                    this.search()
                })
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        search() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'get',
                url: `${this.baseUrl}/vacina-local/search`,
                params: {
                    page: this.pagination.current_page
                }
            }).then(r => {
                this.vacinasLocais = r.data.data
                this.pagination = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        select(vacinaLocal) {
            this.vacinaLocal = vacinaLocal
        }
    }
}

</script>

<style scoped>

</style>
