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
                    <tbody>
                        <tr>
                            <td class="text-left">Id</td>
                            <td class="text-left">Veículo</td>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                        <tr v-for="(veiculo, indexVeiculo) in veiculos">
                            <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;">{{ String(veiculo.VEICULO_ID).padStart(5, '0') }}</td>
                            <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;">{{ veiculo.VEICULO_IDENTIFICACAO }}</td>
                            <td v-if="veiculo.equipe.length > 0">
                                <tr>
                                    <td style="font-weight: bold;">Equipe(s)</td>
                                    <td style="text-align: center;font-weight: bold;">Data</td>
                                    <td style="text-align: center;font-weight: bold;">Turno</td>
                                    <td style="text-align: center;font-weight: bold;">Ação</td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <hr>
                                    </td>
                                </tr>
                                <tr v-for="(eqp, idxEqp) in veiculo.equipe">
                                    <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;width: 78%;padding: 0px 10px 0px 0px;">
                                        <span v-for="(prf, idxPrf) in eqp.equipeProfissional">
                                            {{prf.profissional.PROFISSIONAL_NOME}} - {{ "(" + prf.profissional.tipoProfissional.DESCRICAO + ")" }}
                                            <br>
                                        </span>
                                    </td>
                                    <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;text-align: center;vertical-align: middle!important;width: 10%;">
                                        {{formatarData(veiculo.equipe[idxEqp].EQUIPE_DATA)}}
                                    </td>
                                    <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;text-align: center;vertical-align: middle!important;width: 7%;">
                                        {{veiculo.equipe[idxEqp].EQUIPE_TURNO}}
                                    </td>
                                    <td style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;text-align: center;vertical-align: middle!important;width: 5%;">
                                        <v-btn icon @click="deletar(veiculo.equipe[idxEqp])" title="Remover Equipes">
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
                                    </td>
                                </tr>
                            </td>
                            <td v-else colspan="4" style="border-bottom:solid 0.2px rgba(0, 0, 0, 0.12)!important;">&nbsp;</td>
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
    props:{
        tiposProfissional: {type: Array, default: () => []},
        profissionais: {type: Array, default: () => []},
        tiposVeiculo: {type: Array, default: () => []},
    },
    data() {
        return {
            msgId: 'msgEquipeView',
            msgIdDebug: 'msgEquipeViewDebug',

            idxEqp: 0,
        }
    },
    mounted() {
        this.$store.dispatch('DominioModule/setTipoProfissionais', 
            this.tiposProfissional
        );

        this.$store.dispatch('ProfissionalViewModule/setProfissionais',
            this.profissionais
        )
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
                    veiculo.VEICULO_ATIVO == 1
                        // && veiculo.TG_SITUACAO_VEICULO_ID == 1
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
        preencherComProfissional(equipes) {
            if (!equipes || equipes.length === 0) {
                return '';
            }

            let acumulador = [];
            
            for (let equipe of equipes) {
                for (let prf of equipe.equipeProfissional) {
                    // Cria o texto de cada profissional
                    let nomeProfissional = `${prf.profissional.PROFISSIONAL_NOME} (${prf.profissional.tipoProfissional.DESCRICAO})`;
                    acumulador.push(nomeProfissional);
                }
            }
            
            // Une todos os profissionais colocando um <br> entre eles
            return acumulador.join('\n');
        }

        
    }
}
</script>

<style></style>
