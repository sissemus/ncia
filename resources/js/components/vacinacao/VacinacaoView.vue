<template>
    <div>
        <v-card>
            <div>
                <v-toolbar class="elevation-1">
                    <v-icon class="mr-1">mdi-folder-plus</v-icon>
                    <v-toolbar-title>Vacinação</v-toolbar-title>
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
                                :items="usuarioLocals"
                                item-value="LOCAL_ID"
                                return-object
                                v-model="usuarioLocal"
                                @change="onChangeUsuarioLocal"
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
                                return-object
                                @change="onChangeVacina"
                            >
                                <template v-slot:item="{ item, attrs, on }">
                                    {{ item['VACINA_NOME'] }}
                                </template>
                                <template v-slot:selection="{ item, attrs, on }">
                                    {{ item['VACINA_NOME'] }}
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <v-select
                                label="Estoque"
                                solo
                                hide-details
                                prepend-icon="mdi-needle"
                                :items="vacinaLocais"
                                item-value="VACINA_LOCAL_ID"
                                return-object
                                @change="onChangeVacinaLocal"
                            >
                                <template v-slot:item="{ item, attrs, on }">
                                    {{ formatarDataHora(item['VACINA_LOCAL_DH_CADASTRO']) }} - ({{ item['VACINA_LOCAL_QTD'] }} UN)
                                </template>
                                <template v-slot:selection="{ item, attrs, on }">
                                    {{ formatarDataHora(item['VACINA_LOCAL_DH_CADASTRO']) }} - ({{ item['VACINA_LOCAL_QTD'] }} UN)
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <v-select
                                label="Dose"
                                solo
                                hide-details
                                prepend-icon="mdi-iv-bag"
                                :items="doses"
                                item-value="DOSE_ID"
                                return-object
                                @change="onChangeDose"
                            >
                                <template v-slot:item="{ item, attrs, on }">
                                    {{ item['DOSE_NOME'] }}
                                </template>
                                <template v-slot:selection="{ item, attrs, on }">
                                    {{ item['DOSE_NOME'] }}
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <v-text-field
                               label="Quantidade"
                               hide-details
                               solo
                               prepend-icon="mdi-calculator"
                               v-model="vacinacao.VACINACAO_QTD"
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
                <v-simple-table  v-show="vacinacoes.length" class="mb-0" dense="">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Data</th>
                            <th class="text-left">Dose</th>
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Acoes</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="vacinacao in vacinacoes" :key="vacinacao['clienteId']">
                            <td>{{ vacinacao['VACINACAO_ID'] }}</td>
                            <td>{{ formatarDataHora(vacinacao['VACINACAO_DH']) }}</td>
                            <td>{{ vacinacao['dose']['DOSE_NOME'] }}</td>
                            <td>{{ vacinacao['VACINACAO_QTD'] }}</td>
                            <td>
                                <v-btn icon small @click="select(vacinacao)"><v-icon>mdi-pencil</v-icon></v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapGetters} from "vuex";
import Swal from "sweetalert2";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
name: "VacinacaoView",
    components: {TratarErroAjax},
    mixins: [UtilsMixins],
    props: {
        usuarioLocals: {
            type: Array
        },
        doses: {
            type: Array
        },
    },
    data() {
        return {
            msgId: 'msgVacinacaoView',
            msgIdDebug: 'msgVacinacaoViewDebug',
            vacinaLocais: [],
            vacina: null,
            dose: null,
            vacinas: [],
        }
    },
    mounted() {

    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        usuarioLocal: {
            get() { return this.$store.getters['VacinacaoViewModule/getUsuarioLocal'] },
            set(newValue) { this.$store.dispatch('VacinacaoViewModule/setUsuarioLocal', newValue) }
        },
        vacinacao: {
            get() { return this.$store.getters['VacinacaoViewModule/getVacinacao'] },
            set(newValue) { this.$store.dispatch('VacinacaoViewModule/setVacinacao', newValue) }
        },
        vacinacoes: {
            get() { return this.$store.getters['VacinacaoViewModule/getVacinacoes'] },
            set(newValue) { this.$store.dispatch('VacinacaoViewModule/setVacinacoes', newValue) }
        },
    },
    methods: {
        onChangeUsuarioLocal(usuarioLocal) {
            this.usuarioLocal = usuarioLocal
            console.log('this.usuarioLocal: ', this.usuarioLocal)
            this.getVacinas()
        },
        onChangeVacina(vacina) {
            console.log('vacina: ', vacina)
            this.getVacinasLocaisByVacina(vacina.VACINA_ID)
        },
        onChangeVacinaLocal(vacinaLocal) {
            this.vacinacao.vacinaLocal = vacinaLocal
            this.vacinacao.VACINA_LOCAL_ID = vacinaLocal.VACINA_LOCAL_ID
            this.getVacinacoesByVacinaLocalId(vacinaLocal['VACINA_LOCAL_ID'])
        },
        onChangeDose(dose) {
            this.vacinacao.dose = dose
            this.vacinacao.DOSE_ID = dose['DOSE_ID']
        },
        save() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            let data = JSON.parse(JSON.stringify(this.vacinacao))
            delete data['vacinaLocal']
            delete data['dose']
            axios({
                method: data['VACINACAO_ID'] === null ? 'POST' : 'PUT',
                url: data['VACINACAO_ID'] === null ? `${this.baseUrl}/vacinacao/create` : `${this.baseUrl}/vacinacao/update`,
                data
            }).then(r => {
                console.log(r.data)
                Swal.fire("Sucesso", "Salvo com sucesso", "success").then(() => {
                    this.getVacinacoesByVacinaLocalId(r.data['VACINA_LOCAL_ID'])
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
        getVacinacoesByVacinaLocalId(vacinaLocalId) {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/vacinacao/get-by-vacina-local`,
                params: {
                    vacinaLocalId
                }
            }).then(r => {
                this.vacinacoes = r.data.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        getVacinas() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/vacina/search`,
            }).then(r => {
                this.vacinas = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        getVacinasLocaisByVacina(vacinaId) {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/vacina-local/get-by-vacina`,
                params: {
                    vacinaId: vacinaId
                }
            }).then(r => {
                this.vacinaLocais = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        }
    }
}

</script>

<style scoped>

</style>
