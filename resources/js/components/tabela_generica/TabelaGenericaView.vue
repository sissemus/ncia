<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Tabela Genérica</v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-select
                            label="Tabela"
                            hide-details
                            :items="tabelas"
                            item-value="TABELA_ID"
                            item-text="DESCRICAO"
                            return-object
                            v-model="tabelaSelecionada"
                            @change="listarColunas"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-simple-table dense v-show="colunas.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left">Id</th>
                        <th class="text-left">Descrição</th>
                        <th class="text-left">Ativo</th>
                        <th class="text-left">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="coluna in colunas" :key="coluna['TABELA_GENERICA_ID']">
                        <td>{{ coluna['COLUNA_ID'] }}</td>
                        <td>{{ coluna['DESCRICAO'] }}</td>
                        <td>
                            <v-chip dark color="green" v-if="coluna['ATIVO'] === 1" small>
                                Sim
                            </v-chip>
                            <v-chip dark color="red" v-else small>
                                Não
                            </v-chip>
                        </td>
                        <td>
                            <v-row>
                                <v-col>
                                    <v-btn icon @click="editarColuna(coluna)" title="Editar">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon @click="confirmarRemoverColuna(coluna)" title="Remover">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </td>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>
            <v-divider></v-divider>
            <v-card-actions class="pt-5">
                <v-row>
                    <v-spacer></v-spacer>
                    <v-speed-dial
                        v-model="fab"
                        :top="false"
                        :bottom="true"
                        :right="true"
                        :left="false"
                        direction="top"
                        :open-on-hover="false"
                        transition="slide-y-reverse-transition"
                    >
                        <template v-slot:activator>
                            <v-btn v-model="fab" color="blue darken-2" dark fab>
                                <v-icon v-if="fab">
                                    mdi-close
                                </v-icon>
                                <v-icon v-else>
                                    mdi-hammer-screwdriver
                                </v-icon>
                            </v-btn>
                        </template>
                        <v-btn fab dark small color="indigo" title="Nova coluna" @click="novaColuna">
                            <v-icon>mdi-playlist-plus</v-icon>
                        </v-btn>
                        <v-btn fab dark small color="green darken-3" title="Nova tabela" @click="novaTabela">
                            <v-icon>mdi-database-plus</v-icon>
                        </v-btn>
                    </v-speed-dial>
                </v-row>
            </v-card-actions>
        </v-card>
        <MdNovaColuna></MdNovaColuna>
        <MdNovaTabela></MdNovaTabela>
    </div>
</template>

<script>
import {mapGetters} from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovaColuna from "./MdNovaColuna";
import Swal from "sweetalert2";
import MdNovaTabela from "./MdNovaTabela";

export default {
    name: "TabelaGenericaView",
    components: {MdNovaTabela, MdNovaColuna, TratarErroAjax},
    data() {
        return {
            msgId: 'msgTabelaGenericaView',
            msgIdDebug: 'msgTabelaGenericaViewDebug',
            fab: false
        }
    },
    mounted() {
        this.listarTabelas()
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        tabelas: {
            get() { return this.$store.getters['TabelaGenericaViewModule/getTabelas'] },
            set(newValue) { this.$store.dispatch('TabelaGenericaViewModule/setTabelas', newValue) }
        },
        colunas: {
            get() { return this.$store.getters['TabelaGenericaViewModule/getColunas'] },
            set(newValue) { this.$store.dispatch('TabelaGenericaViewModule/setColunas', newValue) }
        },
        tabelaSelecionada: {
            get() { return this.$store.getters['TabelaGenericaViewModule/getTabelaSelecionada'] },
            set(newValue) { this.$store.dispatch('TabelaGenericaViewModule/setTabelaSelecionada', newValue) }
        }
    },
    methods: {
        listarTabelas() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/tabela_generica/listar`,
                data: this.turno
            })
            .then(r => {
                this.tabelas = r.data['retorno']
            })
            .catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        listarColunas() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/tabela_generica/listar_colunas?tabelaId=${this.tabelaSelecionada.TABELA_ID}&ativos=${0}`
            })
            .then(r => {
                this.colunas = r.data
            })
            .catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        novaTabela() {
            this.$store.dispatch('MdNovaTabelaModule/setShowModal', true)
        },
        novaColuna() {
            this.$store.dispatch('MdNovaColunaModule/setTabela', this.tabelaSelecionada)
            this.$store.dispatch('MdNovaColunaModule/setShowModal', true)
        },
        editarColuna(coluna) {
            this.$store.dispatch('MdNovaColunaModule/setColuna', coluna)
            this.$store.dispatch('MdNovaColunaModule/setShowModal', true)
        },
        confirmarRemoverColuna(coluna) {
            Swal.fire({
                title: 'Confirmação',
                text: "Você confirma a remoção?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#007bff',
                confirmButtonText: 'Sim',
                cancelButtonText: 'Não'
            }).then((result) => {
                if (result.value) {
                    this.removerColuna(coluna)
                }
            })
        },
        removerColuna(coluna) {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'DELETE',
                url: `${this.baseUrl}/tabela_generica/remover_coluna`,
                data: coluna
            })
            .then(r => {
                this.colunas = r.data
                Swal.fire('Sucesso', 'Removido com sucesso', 'success')
            })
            .catch(e => {
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
