<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-folder-plus</v-icon>
                <v-toolbar-title>Detalhes da Fila</v-toolbar-title>
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
                            v-model="localSituacao['LOCAL_ID']"
                            @change="onChangeLocalSituacao"
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
                            label="Status da fila"
                            solo
                            hide-details
                            prepend-icon="mdi-account-group"
                            :items="situacoes"
                            item-value="SITUACAO_ID"
                            item-text="SITUACAO_NOME"
                            v-model="localSituacao['SITUACAO_ID']"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark @click="salvar">
                    SALVAR
                </v-btn>
            </v-card-actions>
        </v-card>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-clock</v-icon>
                <v-toolbar-title>Histórico da Fila</v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>
            <v-card-text>
                <v-simple-table dense v-show="localSituacoes.length" class="mb-0">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Data</th>
                            <th class="text-left">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(localSituacao,i) in localSituacoes" :key="i">
                            <td>{{ localSituacao['LOCAL_SITUACAO_ID'] }}</td>
                            <td>{{ formatarDataHora(localSituacao['LOCAL_SITUACAO_DATA']) }}</td>

                            <td v-if="localSituacao['SITUACAO_ID'] === Situacao.SEM_FILA"><v-chip small dark color="green">{{ localSituacao['situacao']['SITUACAO_NOME'] }}</v-chip></td>
                            <td v-else-if="localSituacao['SITUACAO_ID'] === Situacao.POUCA_FILA"><v-chip small dark color="yellow darken-2">{{ localSituacao['situacao']['SITUACAO_NOME'] }}</v-chip></td>
                            <td v-else-if="localSituacao['SITUACAO_ID'] === Situacao.FILA_MODERADA"><v-chip small dark color="orange">{{ localSituacao['situacao']['SITUACAO_NOME'] }}</v-chip></td>
                            <td v-else-if="localSituacao['SITUACAO_ID'] === Situacao.FILA_INTENSA"><v-chip small dark color="red">{{ localSituacao['situacao']['SITUACAO_NOME'] }}</v-chip></td>
                            <td v-else-if="localSituacao['SITUACAO_ID'] === Situacao.ENCERRADO"><v-chip small dark color="blue-grey lighten-3">{{ localSituacao['situacao']['SITUACAO_NOME'] }}</v-chip></td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card-text>
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
    </div>
</template>

<script>
import TratarErroAjax from "../../js/components/assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";
import LocalSituacao from "../store/modules/payloads/LocalSituacao.json"
import UtilsMixins from "../mixins/UtilsMixins";
import Swal from "sweetalert2";

export default {
name: "Home",
    components: {TratarErroAjax},
    mixins: [UtilsMixins],
    props: {
        usuarioLocais: {
            type: Array,
            required: true
        },
        situacoes: {
            type: Array,
            required: true
        },
        a: {
            type: Array
        }
    },
    data() {
        return {
            msgId: 'msgHome',
            msgIdDebug: 'msgHomeDebug',
            localSituacao: JSON.parse(JSON.stringify(LocalSituacao)),
            localSituacoes: [],
            pagination: {
                current_page: 1,
                total: 0,
                last_page: 0
            }
        }
    },
    mounted() {
        if (this.usuarioLocais.length === 1) {
            this.localSituacao['LOCAL_ID'] = this.usuarioLocais[0]['LOCAL_ID']
            this.getByLocalId()
        }
        console.log('this.a: ', this.a)
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
    },
    methods: {
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            let data = JSON.parse(JSON.stringify(this.localSituacao))
            delete data['local']
            delete data['situacao']
            this.pagination = {
                current_page: 1,
                total: 0,
                last_page: 0
            }
            axios({
                method: 'POST',
                url: `${this.baseUrl}/local_situacao/create`,
                data
            }).then(r => {
                this.localSituacoes = JSON.parse(JSON.stringify(r.data['data']))
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        onPageChange() {
            this.getByLocalId()
        },
        onChangeLocalSituacao() {
            this.pagination = {
                current_page: 1,
                total: 0,
                last_page: 0
            }
            this.getByLocalId()
        },
        getByLocalId() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/local_situacao/listar/local`,
                params: {
                    localId: this.localSituacao['LOCAL_ID'],
                    page: this.pagination.current_page
                }
            }).then(r => {
                console.log(r.data)
                this.localSituacoes = JSON.parse(JSON.stringify(r.data['data']))
                this.pagination = {
                    current_page: r.data.current_page,
                    total: r.data.total,
                    last_page: r.data.last_page
                }
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
