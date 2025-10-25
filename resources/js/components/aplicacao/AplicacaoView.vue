<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Aplicação</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Nova Aplicação" fab small elevation="2" color="primary" dark @click="newRoot">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
<!--            <v-card-text>-->
<!--                <v-row>-->
<!--                    <v-col>-->
<!--                        <v-select-->
<!--                            label="Aplicações"-->
<!--                            hide-details-->
<!--                            :items="aplicacoes"-->
<!--                            item-value="children"-->
<!--                            item-text="APLICACAO_NOME"-->
<!--                            return-object-->
<!--                            @change="selecionarAplicacao"-->
<!--                            append-outer-icon="mdi-pencil"-->
<!--                            @click:append-outer="editarPai"-->
<!--                        ></v-select>-->
<!--                    </v-col>-->
<!--                </v-row>-->
<!--            </v-card-text>-->
            <v-simple-table dense v-show="aplicacao_temp.children.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left">Id</th>
                        <th class="text-left">Descrição</th>
                        <th class="text-left">Ativo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="item in aplicacao_temp.children"
                        :key="item['APLICACAO_ID']"
                        @click="editar(item)"
                        style="cursor: pointer"
                    >
                        <td>{{ item["APLICACAO_ID"] }}</td>
                        <td>{{ item["APLICACAO_NOME"] }}</td>
                        <td>
                            <v-chip
                                dark
                                color="green"
                                v-if="item['APLICACAO_ATIVA'] === 1"
                                small
                            >
                                Sim
                            </v-chip>
                            <v-chip dark color="red" v-else small> Não</v-chip>
                        </td>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>
            <v-divider></v-divider>
        </v-card>

        <v-card>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-treeview
                            v-model="tree"
                            :items="aplicacoes"
                            item-key="APLICACAO_ID"
                            item-text="APLICACAO_NOME"
                            activatable
                            return-object
                            @update:active="ativar"
                            style="cursor: pointer"
                        >
                            <template v-slot:prepend="{ item, open }">
                                <v-icon v-if="!item.file">
                                    {{ item['APLICACAO_ICONE'] }}
                                </v-icon>
                                <v-icon v-else>
                                    {{ files[item.file] }}
                                </v-icon>
                            </template>
                            <template v-slot:append="{ item, open, active }">
                                <v-btn @click="newChild(item)" light fab elevation="2" x-small v-if="item['APLICACAO_PAI_ID'] === null && active">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-treeview>
                    </v-col>
                    <v-divider vertical v-if="aplicacaoSelecionada['APLICACAO_ID'] !== null"></v-divider>
                    <v-col v-if="aplicacaoSelecionada['APLICACAO_ID'] !== null">
                        <v-card class="mx-auto" flat>
                            <v-card-text class="text-center">
                                <v-icon x-large>
                                    {{ aplicacaoSelecionada['APLICACAO_ICONE'] }}
                                </v-icon>
                                <h3 class="headline mb-2">
                                    {{ aplicacaoSelecionada['APLICACAO_NOME'] }}
                                </h3>
                                <div class="blue--text mb-2">
                                    {{ aplicacaoSelecionada['APLICACAO_URL'] }}
                                </div>
                                <div class="blue--text subheading font-weight-bold">
                                    {{ aplicacaoSelecionada[''] }}
                                </div>
                            </v-card-text>
                            <v-divider class="py-2"></v-divider>
                            <v-card-text class="text-center py-1">
                                <v-row>
                                    <v-col>
                                        <v-text-field
                                            label="Aplicação Nome*"
                                            autocomplete="off"
                                            hide-details
                                            v-model="aplicacaoSelecionada.APLICACAO_NOME"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col>
                                        <v-text-field
                                            label="Ordem*"
                                            autocomplete="off"
                                            type="number"
                                            hide-details
                                            v-model="aplicacaoSelecionada.APLICACAO_ORDEM"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col>
                                        <v-text-field
                                            label="Icone"
                                            autocomplete="off"
                                            hide-details
                                            v-model="aplicacaoSelecionada.APLICACAO_ICONE"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row class="text-left">
                                    <v-col>
                                        <v-text-field
                                            label="URL"
                                            autocomplete="off"
                                            hide-details
                                            v-model="aplicacaoSelecionada.APLICACAO_URL"
                                            :prefix="`${baseUrl}/`"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row class="text-left">
                                    <v-col>
                                        <v-select
                                            label="Gestão*"
                                            :items="ativos"
                                            :item-value="'id'"
                                            :item-text="'text'"
                                            v-model="aplicacaoSelecionada.APLICACAO_GESTAO"
                                        >
                                        </v-select>
                                    </v-col>
                                    <v-col>
                                        <v-select
                                            label="Ativo*"
                                            :items="ativos"
                                            :item-value="'id'"
                                            :item-text="'text'"
                                            v-model="aplicacaoSelecionada.APLICACAO_ATIVA"
                                        >
                                        </v-select>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" dark tile @click="salvar">
                                    Salvar
                                </v-btn>
                                <v-btn color="red" dark tile @click="consfirmarRemocao">
                                    Remover
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <MdNovaAplicacao></MdNovaAplicacao>
    </div>
</template>

<script>
import {mapGetters} from "vuex";
import MdNovaAplicacao from './MdNovaAplicacao';
import TratarErroAjax from "../assets/TratarErroAjax";
import Aplicacao from "../../store/modules/payloads/Aplicacao.json";
import Swal from "sweetalert2";

export default {
    name: "AplicacaoView",
    components: {MdNovaAplicacao, TratarErroAjax},
    data() {
        return {
            msgId: 'msgAfastamentoView',
            msgIdDebug: 'msgAfastamentoViewDebug',
            fab: false,
            aplicacao_temp: {
                children: []
            },
            tree: [],
            ativo: [],
            aplicacaoSelecionada: JSON.parse(JSON.stringify(Aplicacao)),
            ativos: [
                {id: 1, text: "Sim"},
                {id: 0, text: "Não"},
            ],
        }
    },
    mounted() {
        this.listar();
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        aplicacoes: {
            get() {
                return this.$store.getters['AplicacaoViewModule/getAplicacoes'];
            },
            set(value) {
                this.$store.dispatch('AplicacaoViewModule/setAplicacoes', value);
            },
        },
    },
    methods: {
        listar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/aplicacao/list`,
            })
                .then(r => {
                    this.aplicacoes = r.data
                })
                .catch(e => {
                    console.error('ERRO: ', e)
                    this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                        id: this.msgId,
                        response: e.response
                    })
                })
        },
        abrirModal() {
            this.$store.dispatch('MdNovaAplicacaoModule/setShowModal', true);
        },
        selecionarAplicacao(dado) {
            this.aplicacao_temp = dado;
        },
        editarPai() {
            this.editar(this.aplicacao_temp)
        },
        editar(item) {
            this.$store.dispatch("MdNovaAplicacaoModule/setAplicacao", item);
            this.$store.dispatch('MdNovaAplicacaoModule/setShowModal', true);
        },
        ativar(e) {
            if (e[0] === undefined) {
                this.aplicacaoSelecionada = JSON.parse(JSON.stringify(Aplicacao))
            } else {
                this.aplicacaoSelecionada = JSON.parse(JSON.stringify(e[0]))
            }
        },
        salvar() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
            axios({
                method: this.aplicacaoSelecionada.APLICACAO_ID === null ? "POST" : "PUT",
                url:
                    this.aplicacaoSelecionada.APLICACAO_ID === null
                        ? `${this.baseUrl}/aplicacao/create`
                        : `${this.baseUrl}/aplicacao/update`,
                data: this.aplicacaoSelecionada,
            }).then((r) => {
                // this.listar(1);
                this.aplicacoes = r.data;
                Swal.fire("Sucesso", "Salvo com sucesso", "success");
            }).catch((e) => {
                console.error("ERRO: ", e);
                this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                    id: this.msgId,
                    response: e.response,
                });
            });
        },
        async newChild(item) {
            const {value: aplicacaoNome} = await Swal.fire({
                title: 'Aplicação Filha',
                input: 'text',
                inputLabel: 'Digite o nome da aplicação',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Você precisa informar um nome'
                    }
                }
            })

            if (aplicacaoNome) {
                axios({
                    method: 'POST',
                    url: `${this.baseUrl}/aplicacao/create`,
                    data: {
                        APLICACAO_ID: null,
                        APLICACAO_NOME: aplicacaoNome,
                        APLICACAO_ICONE: 'mdi-menu',
                        APLICACAO_URL: null,
                        APLICACAO_GESTAO: 0,
                        APLICACAO_ATIVA: 1,
                        APLICACAO_ORDEM: item['children'].length + 1,
                        APLICACAO_PAI_ID: item['APLICACAO_ID'],
                    }
                }).then(r => {
                    this.aplicacoes = r.data;
                    Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
                }).catch(e => {
                    console.error('ERRO: ', e)
                    this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                        id: this.msgId,
                        response: e.response
                    })
                })
            }
        },
        async newRoot() {
            const {value: aplicacaoNome} = await Swal.fire({
                title: 'Aplicação Pai',
                input: 'text',
                inputLabel: 'Digite o nome da aplicação',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Você precisa informar um nome'
                    }
                }
            })

            if (aplicacaoNome) {
                axios({
                    method: 'POST',
                    url: `${this.baseUrl}/aplicacao/create`,
                    data: {
                        APLICACAO_ID: null,
                        APLICACAO_NOME: aplicacaoNome,
                        APLICACAO_ICONE: 'mdi-menu',
                        APLICACAO_URL: null,
                        APLICACAO_GESTAO: 0,
                        APLICACAO_ATIVA: 1,
                        APLICACAO_ORDEM: this.aplicacoes.length + 1,
                        APLICACAO_PAI_ID: null,
                    }
                }).then(r => {
                    this.aplicacoes = r.data;
                    Swal.fire('Sucesso', 'Salvo com sucesso', 'success')
                }).catch(e => {
                    console.error('ERRO: ', e)
                    this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                        id: this.msgId,
                        response: e.response
                    })
                })
            }
        },
        consfirmarRemocao() {
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
                    this.remover()
                }
            })
        },
        remover() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'DELETE',
                url: `${this.baseUrl}/aplicacao/delete`,
                data: {
                    aplicacaoId: this.aplicacaoSelecionada['APLICACAO_ID']
                }
            }).then(r => {
                this.aplicacoes = r.data;
                this.aplicacaoSelecionada = JSON.parse(JSON.stringify(Aplicacao))
                Swal.fire('Sucesso', 'Removido com sucesso', 'success')
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

<style>
</style>
