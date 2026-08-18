<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Equipes</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Nova equipe" fab small elevation="2" color="primary" dark @click="novoEquipe">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Equipe" autocomplete="off" hide-details
                            v-model="equipePesquisa.EQUIPE_ID"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>

                </v-row>
                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-simple-table dense v-show="veiculos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Veículo</th>
                            <th class="text-left">Equipe</th>
                            <th class="text-center">Data</th>
                            <th class="text-center">Turno</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="veiculo in veiculos">
                            <td>{{ veiculo.VEICULO_ID }}</td>
                            <td>{{ veiculo.VEICULO_IDENTIFICACAO }}</td>
                            <td v-if="veiculo.equipe">
                                <tr v-for="prf in veiculo.equipe.equipeProfissional">
                                    <td>{{ prf ? prf.profissional.PROFISSIONAL_NOME : '' }} - 
                                    {{ prf && prf.profissional.tipoProfissional ? prf.profissional.tipoProfissional.DESCRICAO : '' }}</td>
                                </tr>
                            </td>
                            <td v-else></td>
                            <td class="text-center">{{ veiculo.equipe ? formatarData(veiculo.equipe.EQUIPE_DATA) : '' }}</td>
                            <td class="text-center">{{ veiculo.equipe ? veiculo.equipe.EQUIPE_TURNO : '' }}</td>
                            <td>
                                <v-btn v-if="veiculo.equipe" icon @click="deletar(veiculo)" title="Remover Equipes">
                                    <v-icon>mdi-delete</v-icon>
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
        <MdNovoEquipe></MdNovoEquipe>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoEquipe from "./MdNovoEquipe";

export default {
    name: "EquipeView",
    components: { MdNovoEquipe, TratarErroAjax },
    data() {
        return {
            msgId: 'msgEquipeView',
            msgIdDebug: 'msgEquipeViewDebug',
        }
    },
    mounted() {
        // Carga inicial
        // this.search();
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        equipes: {
            get() { return this.$store.getters['EquipeViewModule/getEquipes'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setEquipes', newValue) }
        },
        pagination: {
            // get() { return this.$store.getters['EquipeViewModule/getPagination'] },
            // set(newValue) { this.$store.dispatch('EquipeViewModule/setPagination', newValue) }
            get() { return this.$store.getters['VeiculoViewModule/getVeiculos']},
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setVeiculoPesquisa', newValue) }
        },
        equipePesquisa: {
            get() { return this.$store.getters['EquipeViewModule/getEquipePesquisa'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setEquipePesquisa', newValue) }
        },
        veiculoPesquisa: {
            get() { return this.$store.getters['VeiculoViewModule/getVeiculoPesquisa'] },
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setVeiculoPesquisa', newValue) }
        },
        veiculos: {
            get() {
                return this.$store.getters['VeiculoViewModule/getVeiculos']
                    .filter(veiculo =>
                        veiculo.TG_SITUACAO_VEICULO_ID == 1 &&
                        veiculo.VEICULO_ATIVO == 1
                    )
            },
            set(newValue) {
                this.$store.dispatch(
                    'VeiculoViewModule/setVeiculoPesquisa',
                    newValue
                )
            }
        }
    },
    methods: {
        search() {
            // this.$store.dispatch('EquipeViewModule/search', this.msgId);
            this.$store.dispatch('VeiculoViewModule/search', {
                msgId: this.msgId,
                TG_SITUACAO_VEICULO_ID: 1,
                VEICULO_ATIVO: 1
            });
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.veiculoPesquisa = {
                VEICULO_ID: null,
            };
            this.pagination.current_page = 1;
            this.search();
        },

        novoEquipe() {
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        selecionar(equipe) {
            this.$store.dispatch('MdNovoEquipeModule/setEquipe', equipe)
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        deletar(veiculo) {
            let params = {
                EQUIPE_ID: veiculo.equipe.EQUIPE_ID
            }

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir a equipe do veículo ${veiculo.VEICULO_IDENTIFICACAO} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            }).then(result => {
                if (result.isConfirmed)
                    axios.delete(`${this.baseUrl}/equipe/deletar`, { params })
                        .then(res => {
                            Swal.fire('Excluído com sucesso!', '', 'success')
                                .then(res => {
                                    this.search();
                                })
                        })
            })
        },

        truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        },
        formatarData(data) {
            if (!data) {
                return '';
            }

            const dataParte = data.substring(0, 10);
            const partes = dataParte.split('-');

            if (partes.length !== 3) {
                return '';
            }

            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        },   
    }
}
</script>

<style></style>
