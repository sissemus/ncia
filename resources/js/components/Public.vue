<template>
    <div>
        <v-app id="inspire">
            <v-app-bar app color="gray" light extended class="pa-5">
                <v-container>
                    <v-layout row>
                        <h2 style="color: #2071B7"><b>PROJETO BASE</b></h2>
                    </v-layout>
                    <v-layout row>
                        <h5 style="color: #2071B7"><b>CONSULTA DE VACINAS APLICADAS</b></h5>
                    </v-layout>
                </v-container>

                <v-spacer></v-spacer>
                <div class="d-flex align-center">
                    <v-img alt="Prefeitura de São Luís" class="shrink mr-0" contain :src="baseUrl+'/img/logo_topo.png'" transition="scale-transition" width="150"/>
                </div>
            </v-app-bar>

            <v-main style="background-color: #eee">
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <div :id="msgIdDebug"></div>


                <v-container class="pt-10" v-show="mostrar">
<!--                    <v-card class="mb-5" elevation="0" color="green" dark shaped outlined>-->
<!--                        <v-card-text>-->
<!--                            <v-row>-->
<!--                                <v-col class="text-right">-->
<!--                                    <h1><v-icon class="mr-1">mdi-needle</v-icon>POSTOS ABERTOS: {{ totalPostosAtivos }}</h1>-->
<!--                                </v-col>-->
<!--                            </v-row>-->
<!--                        </v-card-text>-->
<!--                    </v-card>-->

                    <v-toolbar class="elevation-0 mb-5" color="#2071B7" dark shaped>
                        <v-icon class="mr-1">mdi-car-multiple</v-icon>
                        <v-toolbar-title>DRIVE</v-toolbar-title>
                        <v-spacer></v-spacer>
                    </v-toolbar>
                    <template v-if="locaisDts.length === 0">
                        <v-alert border="left" colored-border color="blue-grey lighten-3" elevation="2">
                            Não há postos ativos
                        </v-alert>
                    </template>
                    <template v-else>
<!--                        <v-alert v-for="local in locaisDts" :key="local['LOCAL_ID']" border="left" colored-border :color="getStatusColor(local['localSituacaoUltima'])" elevation="2">-->
<!--                            <v-row>-->
<!--                                <v-col cols="12" sm="12" md="8" lg="8">-->
<!--                                    <p title="DRIVE-THRU" v-if="local['LOCAL_TIPO'] === LocalTipo.DRIVE_THRU"><v-icon>mdi-car-multiple</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>-->
<!--                                    <p title="CENTRO MUNICIPAL DE VACINAÇÃO" v-else-if="local['LOCAL_TIPO'] === LocalTipo.CMV"><v-icon>mdi-hospital-building</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>-->

<!--                                    <p title="LOCAL"><v-icon>mdi-map-marker</v-icon> {{ local['LOCAL_ENDERECO'] }}</p>-->
<!--                                    <p title="FUNCIONAMENTO"><v-icon>mdi-clock</v-icon> <b>Abertura:</b> {{ local['LOCAL_ABERTURA'] }} <b>Fechamento:</b> {{ local['LOCAL_FECHAMENTO'] }}</p>-->
<!--                                    <p title="PÚBLICO ALVO"><v-icon>mdi-bullseye-arrow</v-icon> {{ local['publicoUltimo'] === null ? '' : local['publicoUltimo']['PUBLICO_DESCRICAO'] }}</p>-->
<!--                                </v-col>-->
<!--                                <v-divider></v-divider>-->

<!--                                <v-col cols="12" sm="12" md="2" lg="2">-->
<!--                                    <p><b class="text&#45;&#45;accent-1&#45;&#45;">SITUAÇÃO</b></p>-->
<!--                                    <p><v-chip dark label small :color="getStatusColor(local['localSituacaoUltima'])">{{ local['localSituacaoUltima'] === null ? '' : local['localSituacaoUltima']['situacao']['SITUACAO_NOME'] }}</v-chip></p>-->
<!--                                </v-col>-->
<!--                                <v-divider></v-divider>-->

<!--                                <v-col cols="12" sm="12" md="2" lg="2">-->
<!--                                    <p><b>ATUALIZADO</b></p>-->
<!--                                    <p>{{ local['localSituacaoUltima'] === null ? '' : formatarDataHora(local['localSituacaoUltima']['LOCAL_SITUACAO_DATA']) }}</p>-->
<!--                                </v-col>-->
<!--                            </v-row>-->
<!--                        </v-alert>-->
                        <v-alert v-for="(local) in locaisDts" :key="local.LOCAL_ID" border="left" colored-border color="green" elevation="2">
                            <v-row>
                                <v-col cols="12" sm="12" md="8" lg="8">
                                    <p title="DRIVE-THRU" v-if="local['LOCAL_TIPO'] === LocalTipo.DRIVE_THRU"><v-icon>mdi-car-multiple</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>
                                    <p title="CENTRO MUNICIPAL DE VACINAÇÃO" v-else-if="local['LOCAL_TIPO'] === LocalTipo.CMV"><v-icon>mdi-hospital-building</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>

                                     <p title="LOCAL"><v-icon>mdi-map-marker</v-icon> {{ local['LOCAL_ENDERECO'] }}</p>
                                    <p title="FUNCIONAMENTO"><v-icon>mdi-clock</v-icon> <b>Abertura:</b> {{ local['LOCAL_ABERTURA'] }} <b>Fechamento:</b> {{ local['LOCAL_FECHAMENTO'] }}</p>

                                </v-col>
                                <v-divider></v-divider>
                            </v-row>
                            <v-simple-table  v-show="false" class="mb-0">
                                <template v-slot:default>
                                    <tbody>
                                    <tr v-for="(vacinaLocal, j) in local['vacinaLocais']" :key="j" style="cursor: pointer">
                                        <td> <v-chip color="orange" dark><v-icon>mdi-needle</v-icon> {{ vacinaLocal['vacina']['VACINA_NOME'] }}</v-chip> </td>
                                        <td>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="text-left" style="width: 150px;" v-for="(vacinacao, k) in vacinaLocal['vacinacoes']" :key="k"><v-chip color="primary">{{ vacinacao['dose']['DOSE_NOME'] }}</v-chip></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-left" v-for="(vacinacao, c) in vacinaLocal['vacinacoes']" :key="c"><v-chip>{{ vacinacao['VACINACAO_QTD'] }}</v-chip></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>

                            <v-card v-for="(vacinaLocal, j) in local['vacinaLocais']" :key="j" class="mb-2">
                                    <v-card-title>
                                        <th>
                                            <v-chip color="orange" dark><v-icon>mdi-needle</v-icon> {{ vacinaLocal['vacina']['VACINA_NOME'] }}</v-chip>
                                        </th>
                                    </v-card-title>
                                <v-card-text>
                            <v-simple-table >
                                <thead>
                                    <tr >
                                        <th>Dose</th>
                                        <th>Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(vacinacao, c) in vacinaLocal['vacinacoes']" :key="c">
                                        <td>{{ vacinacao['dose']['DOSE_NOME'] }}</td>
                                        <td>{{ vacinacao['VACINACAO_QTD'] }}</td>
                                    </tr>
                                </tbody>
                            </v-simple-table>

                                </v-card-text>
                            </v-card>

                        </v-alert>


                    </template>


                    <v-toolbar class="elevation-0 mb-5" color="#2071B7" dark shaped>
                        <v-icon class="mr-1">mdi-hospital-building</v-icon>
                        <v-toolbar-title>PONTO FIXO</v-toolbar-title>
                        <v-spacer></v-spacer>
                    </v-toolbar>
                    <template v-if="locaisCmv.length === 0">
                        <v-alert border="left" colored-border color="blue-grey lighten-3" elevation="2">
                            Não há postos ativos
                        </v-alert>
                    </template>
                    <template v-else>
                      <v-alert v-for="(local) in locaisCmv" :key="local.LOCAL_ID" border="left" colored-border color="green" elevation="2">
                            <v-row>
                                <v-col cols="12" sm="12" md="8" lg="8">
                                    <p title="DRIVE-THRU" v-if="local['LOCAL_TIPO'] === LocalTipo.DRIVE_THRU"><v-icon>mdi-car-multiple</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>
                                    <p title="CENTRO MUNICIPAL DE VACINAÇÃO" v-else-if="local['LOCAL_TIPO'] === LocalTipo.CMV"><v-icon>mdi-hospital-building</v-icon> <b>{{ local['LOCAL_DESCRICAO'] }}</b></p>

                                     <p title="LOCAL"><v-icon>mdi-map-marker</v-icon> {{ local['LOCAL_ENDERECO'] }}</p>
                                    <p title="FUNCIONAMENTO"><v-icon>mdi-clock</v-icon> <b>Abertura:</b> {{ local['LOCAL_ABERTURA'] }} <b>Fechamento:</b> {{ local['LOCAL_FECHAMENTO'] }}</p>

                                </v-col>
                                <v-divider></v-divider>
                            </v-row>

                            <v-simple-table  v-show="false" class="mb-0">
                                <template v-slot:default>
                                    <tbody>
                                    <tr v-for="(vacinaLocal, j) in local['vacinaLocais']" :key="j" style="cursor: pointer">
                                        <td> <v-chip color="orange" dark><v-icon>mdi-needle</v-icon> {{ vacinaLocal['vacina']['VACINA_NOME'] }}</v-chip> </td>
                                        <td>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="text-left" style="width: 150px;" v-for="(vacinacao, k) in vacinaLocal['vacinacoes']" :key="k"><v-chip color="primary">{{ vacinacao['dose']['DOSE_NOME'] }}</v-chip></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-left" v-for="(vacinacao, c) in vacinaLocal['vacinacoes']" :key="c"><v-chip>{{ vacinacao['VACINACAO_QTD'] }}</v-chip></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>

                            <v-card v-for="(vacinaLocal, j) in local['vacinaLocais']" :key="j" class="mb-2">
                                    <v-card-title>
                                        <th>
                                            <v-chip color="orange" dark><v-icon>mdi-needle</v-icon> {{ vacinaLocal['vacina']['VACINA_NOME'] }}</v-chip>
                                        </th>
                                    </v-card-title>
                                <v-card-text>
                            <v-simple-table >
                                <thead>
                                    <tr >
                                        <th>Dose</th>
                                        <th>Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(vacinacao, c) in vacinaLocal['vacinacoes']" :key="c">
                                        <td>{{ vacinacao['dose']['DOSE_NOME'] }}</td>
                                        <td>{{ vacinacao['VACINACAO_QTD'] }}</td>
                                    </tr>
                                </tbody>
                            </v-simple-table>

                                </v-card-text>
                            </v-card>
                        </v-alert>
                    </template>
                </v-container>
            </v-main>
            <v-divider></v-divider>
            <v-footer light padless>
                <v-card flat tile class="text-center" style="width: 100%">
                    <v-card-text>
                        <v-btn class="mx-4" icon href="https://twitter.com/prefeiturasl" target="_blank">
                            <v-icon size="24px">
                                mdi-twitter
                            </v-icon>
                        </v-btn>
                        <v-btn class="mx-4" icon href="https://www.facebook.com/PrefeituraDeSaoLuis" target="_blank">
                            <v-icon size="24px">
                                mdi-facebook
                            </v-icon>
                        </v-btn>
                        <v-btn class="mx-4" icon href="https://www.instagram.com/prefeiturasaoluis/" target="_blank">
                            <v-icon size="24px">
                                mdi-instagram
                            </v-icon>
                        </v-btn>
                    </v-card-text>

                    <v-card-text class="pt-0">
                        PREFEITURA DE SÃO LUÍS<br>
                        Av. Pedro II, S/N° - Palácio De La Ravardière - Centro - São Luís - MA - CEP: 65010-904
                    </v-card-text>

                    <v-card-text>
                        {{ new Date().getFullYear() }} — SEMIT
                    </v-card-text>
                    <v-img :src="baseUrl+'/img/predios3.png'"></v-img>
                </v-card>
            </v-footer>

            <block-u-i></block-u-i>
        </v-app>
        <MdOrientacoes></MdOrientacoes>
        <MdGraficoSituacaoFila></MdGraficoSituacaoFila>
    </div>
</template>

<script>
import BlockUI from "./assets/BlockUI";
import {mapGetters} from "vuex";
import TratarErroAjax from "./assets/TratarErroAjax";
import UtilsMixins from "../mixins/UtilsMixins";
import MdOrientacoes from "./MdOrientacoes";
import MdGraficoSituacaoFila from "./grafico/MdGraficoSituacaoFila";

export default {
    name: "Public",
    mixins: [UtilsMixins],
    components: {MdGraficoSituacaoFila, MdOrientacoes, TratarErroAjax, BlockUI},
    props: {
        totalPostosAtivos: {
            type: Number,
            required: true
        },
        locais: {
            type: Array,
        }
    },
    data() {
        return {
            msgId: "msgPublic",
            msgIdDebug: "msgPublicDebug",
            // locaisCmv: [],
            // locaisDts: [],
            mostrar: true
        }
    },
    mounted() {
        // this.listarLocais()
        console.log('locais: ', this.locais);
    },
    created() {
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
            return Promise.reject(error);
        });
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),
        locaisCmv(){
            return this.locais.filter(local => local.LOCAL_TIPO == 1);
        },
        locaisDts(){
            return this.locais.filter(local => local.LOCAL_TIPO == 2);
        },
    },
    methods: {
        listarLocais() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/local/list`,
            }).then(r => {
                console.log(r.data)
                this.locaisDts = JSON.parse(JSON.stringify(r.data['dts']))
                this.locaisCmv = JSON.parse(JSON.stringify(r.data['cmvs']))
                this.mostrar = true
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        getStatusColor(localSituacaoUltima) {
            if (localSituacaoUltima) {
                switch (localSituacaoUltima['SITUACAO_ID']) {
                    case UtilsMixins.data().Situacao.SEM_FILA:
                        return 'green'
                    case UtilsMixins.data().Situacao.POUCA_FILA:
                        return 'yellow darken-1'
                    case UtilsMixins.data().Situacao.FILA_MODERADA:
                        return 'orange'
                    case UtilsMixins.data().Situacao.FILA_INTENSA:
                        return 'red'
                    case UtilsMixins.data().Situacao.ENCERRADO:
                        return 'blue-grey lighten-3'
                }
            }
            // return 'deep-purple accent-4'
            return 'grey accent-4'
        },
        getStatusColor2(localSituacaoUltima) {
            if (localSituacaoUltima) {
                let situacaoId = parseInt(localSituacaoUltima['SITUACAO_ID'])
                switch (situacaoId) {
                    case UtilsMixins.data().Situacao.SEM_FILA:
                        return 'primary'
                    case UtilsMixins.data().Situacao.POUCA_FILA:
                        return 'green'
                    case UtilsMixins.data().Situacao.FILA_MODERADA:
                        return 'orange'
                    case UtilsMixins.data().Situacao.FILA_INTENSA:
                        return 'red'
                    case UtilsMixins.data().Situacao.ENCERRADO:
                        return 'blue-grey lighten-3'
                }
            }
            // return 'deep-purple accent-4'
            return 'grey accent-4'
        },
        grafico(local) {
            this.$store.dispatch("MdGraficoSituacaoFilaModule/setLocal", local)
            this.$store.dispatch("MdGraficoSituacaoFilaModule/setOptions", local.options)
            this.$store.dispatch("MdGraficoSituacaoFilaModule/setChartdata", local.chartdata)
            this.$store.dispatch("MdGraficoSituacaoFilaModule/setShowModal", true)
        }
    }
}
</script>

<style scoped>

</style>
