<template>
    <div>
        <v-row justify="center">
<<<<<<< HEAD
            <v-dialog
                v-model="showModal"
                persistent
                width="800"
                scrollable
                :fullscreen="fullScreen"
            >
                <v-card>
                    <v-toolbar
                        color="primary"
                        elevation="1"
                        class="flex-grow-0"
                        dark
                    >
                        <v-toolbar-title>
                            Equipe
                        </v-toolbar-title>

                        <v-spacer></v-spacer>

                        <v-btn
                            icon
                            @click="fullScreen = true"
                            v-show="fullScreen === false"
                        >
                            <v-icon>
                                mdi-window-maximize
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="fullScreen = false"
                            v-show="fullScreen === true"
                        >
                            <v-icon>
                                mdi-window-restore
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="clearFormAndClose"
                        >
                            <v-icon>
                                mdi-close
                            </v-icon>
                        </v-btn>
                    </v-toolbar>

                    <tratar-erro-ajax
                        :id="msgId"
                    ></tratar-erro-ajax>

                    <div :id="msgIdDebug"></div>

                    <v-card-text class="mt-5">
                        <v-row>
                            <v-col cols="8">
                                <v-select
                                    label="Veículo*"
                                    :items="veiculos"
                                    item-value="VEICULO_ID"
                                    item-text="VEICULO_IDENTIFICACAO"
                                    v-model="equipe.VEICULO_ID"
                                ></v-select>
                            </v-col>
                            <v-col cols="4">
                                <v-select 
                                    label="Turno*" 
                                    item-value="value" 
                                    item-text="text" 
                                    v-model="equipe.EQUIPE_TURNO"
                                    :items="opcoesTurno"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="11">
                                <v-select
                                    label="Profissional*"
                                    :items="profissionalEspecifico"
                                    item-value="value"
                                    item-text="text"
                                    v-model="equipe.PROFISSIONAL_ID"
                                ></v-select>
                            </v-col>
                            <v-col cols="1" style="text-align: right;">
                                <v-btn title="Adicionar profissional" fab small elevation="3" color="success" dark @click="novoProfissional">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                        <v-simple-table dense v-show="profissionaisSelecionados.length" class="mb-0">
                            <template v-slot:default>
                                <thead>
                                    <tr>
                                        <th class="text-left">Id</th>
                                        <th class="text-left">Profissional</th>
                                        <th class="text-left">Tipo de Profissional</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in profissionaisSelecionados" :key="equipe['EQUIPE_ID']">
                                        <td>{{ row['PROFISSIONAL_ID'] }}</td>

                                        <td>
                                            {{ row.profissional.PROFISSIONAL_NOME }}
                                        </td>
                                        <td>
                                            {{ row.profissional.tipoProfissional ? row.profissional.tipoProfissional.DESCRICAO : '' }}
                                        </td>                            

                                        <td>
                                            <v-btn icon @click="deletarProfissional(row)" title="Remover">
                                                <v-icon>mdi-delete</v-icon>
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </template>
                        </v-simple-table>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primary"
                            dark
                            tile
                            @click="salvar"
                        >
                            salvar
                        </v-btn>

                        <v-btn
                            color="red"
                            dark
                            outlined
                            tile
                            @click="clearFormAndClose"
                        >
=======
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes da Equipe</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>
                    <v-card-text class="mt-5">
                        <v-row>
                            <v-col>
                                <v-select label="Ativo*" :items="ativos" :item-value="'id'" :item-text="'text'"
                                    v-model="equipe.EQUIPE_ATIVO">
                                </v-select>
                            </v-col>
                            <v-col>
                                <v-select
                                label="Veículo*"
                                :items="veiculos"
                                item-value="VEICULO_ID"
                                item-text="VEICULO_IDENTIFICACAO"
                                v-model="equipe.VEICULO_ID"
                                ></v-select>
                                
                            </v-col>
                            <v-col>
                                <v-select 
                                    label="Profissional*" 
                                    :items="profissionais" 
                                    item-value="PROFISSIONAL_ID" 
                                    item-text="PROFISSIONAL_NOME"
                                    v-model="equipe.PROFISSIONAL_ID">
                                </v-select>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider class="ma-0"></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">
                            salvar
                        </v-btn>
                        <v-btn color="red" dark outlined tile @click="clearFormAndClose">
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
<<<<<<< HEAD
import { mapGetters } from 'vuex';
import TratarErroAjax from '../assets/TratarErroAjax';
import Swal from 'sweetalert2';

export default {
    name: 'MdNovoEquipe',

    components: {
        TratarErroAjax
    },

=======
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";

export default {
    name: "MdNovoEquipe",
    components: { TratarErroAjax },
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
    data() {
        return {
            msgId: 'msgMdNovoEquipe',
            msgIdDebug: 'msgMdNovoEquipeDebug',
<<<<<<< HEAD

=======
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
            ativos: [
                {
                    id: 1,
                    text: 'Sim'
                },
                {
                    id: 0,
                    text: 'Não'
                }
            ],
<<<<<<< HEAD

            opcoesTurno: [
                { 
                    text: 'SD', 
                    value: 'SD' 
                },
                { 
                    text: 'SN', 
                    value: 'SN' 
                }
            ],
            profissionaisSelecionados:[]
        }
    },
    mounted() {
        this.$store.dispatch(
            'VeiculoViewModule/search',
            {
                msgId: this.msgId,
                VEICULO_ATIVO: 1
            }
=======
        }
    },
    mounted() {
        console.log('MdNovoEquipe montado');

        this.$store.dispatch(
            'VeiculoViewModule/search',
            this.msgId
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
        );

        this.$store.dispatch(
            'ProfissionalViewModule/search',
<<<<<<< HEAD
            {
                msgId: this.msgId,
                PROFISSIONAL_ATIVO: 1
            }
            
        );
    },

=======
            this.msgId
        );
    },
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
<<<<<<< HEAD

        showModal: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getShowModal'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setShowModal',
                    valor
                );
            }
        },

        fullScreen: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getFullScreen'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setFullScreen',
                    valor
                );
            }
        },

        equipe: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getEquipe'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setEquipe',
                    valor
                );
            }
        },

        veiculos() {
            return this.$store.getters['VeiculoViewModule/getVeiculos']
                .filter(veiculo =>
                    veiculo.TG_SITUACAO_VEICULO_ID == 1 &&
                    veiculo.VEICULO_ATIVO == 1
                )
        },

        profissionais() {
            return this.$store.getters[
                'ProfissionalViewModule/getProfissionais']
                .filter(profissional => 
                profissional.PROFISSIONAL_ATIVO == 1
            );
        },

        dataFormatada: {
            get() {
                return this.formatarDataParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA
                );
            },

            set(valor) {
                this.atualizarData(
                    'EQUIPE_DATA',
                    valor
                );
            }
        },
        profissionalEspecifico() {
            // Verifica se a lista existe e não está vazia
            if (!this.profissionais || !Array.isArray(this.profissionais)) {
                return [];
            }

            // Transforma a lista antiga no formato que o Vuetify precisa
            return this.profissionais.map(item => {
                // Monta o texto unindo Nome + Descrição (se a descrição existir)
                const descricao = item.tipoProfissional ? ` (${item.tipoProfissional.DESCRICAO})` : '';
                
                return {
                    value: item.PROFISSIONAL_ID,
                    text: `${item.PROFISSIONAL_NOME}${descricao}`
                };
            });
        }
    },

    methods: {
        clearFormAndClose() {
            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );

            this.equipe = null;
            this.showModal = false;
        },

        salvar() {
            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );

            // if (!this.equipe) {
            //     return;
            // }

            if (!this.profissionaisSelecionados) {
                return;
            }

            // const equipe = this.equipe

            const profissionaisSelecionados = this.profissionaisSelecionados

            const dados = {
                ...profissionaisSelecionados
                
            };

            const estaEditando =
                dados.EQUIPE_ID !== null &&
                dados.EQUIPE_ID !== undefined;

            axios({
                method: estaEditando
                    ? 'PUT'
                    : 'POST',

                url: estaEditando
                    ? `${this.baseUrl}/equipe/alterar`
                    : `${this.baseUrl}/equipe/inserir`,

                data: dados
            })
            .then(() => {
                this.clearFormAndClose();

                Swal.fire(
                    'Sucesso',
                    'Salvo com sucesso',
                    'success'
                ).then(() => {
                    this.$store.dispatch(
                        'EquipeViewModule/search',
                        this.msgId
                    );
                    this.$store.dispatch(
                        'VeiculoViewModule/search',
                        this.msgId
                    );
                });
            })
            .catch(error => {
                console.error(
                    'ERRO: ',
                    error
                );

                this.$store.dispatch(
                    'TratarErroAjaxModule/tratarErro',
                    {
                        id: this.msgId,
                        response: error.response
                    }
                );
            });
        },

        formatarDataParaInput(valor) {
            if (!valor) {
                return '';
            }

            /*
             * Caso o Laravel retorne:
             *
             * 2026-08-12 00:00:00.000
             */
            if (
                /^\d{4}-\d{2}-\d{2} /.test(valor)
            ) {
                return valor.substring(0, 10);
            }

            /*
             * Caso venha no formato:
             *
             * 12-08-2026
             */
            if (
                /^\d{2}-\d{2}-\d{4}/.test(valor)
            ) {
                const partes = valor
                    .substring(0, 10)
                    .split('-');

                return [
                    partes[2],
                    partes[1],
                    partes[0]
                ].join('-');
            }

            /*
             * Caso o Laravel retorne ISO:
             *
             * 2026-08-12T03:00:00.000000Z
             */
            const iso = valor.replace(
                /\.(\d{3})\d+Z$/,
                '.$1Z'
            );

            const data = new Date(iso);

            if (isNaN(data.getTime())) {
                return '';
            }

            const partes = new Intl.DateTimeFormat(
                'en-CA',
                {
                    timeZone: 'America/Fortaleza',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }
            ).formatToParts(data);

            const ano = partes.find(
                item => item.type === 'year'
            ).value;

            const mes = partes.find(
                item => item.type === 'month'
            ).value;

            const dia = partes.find(
                item => item.type === 'day'
            ).value;

            return `${ano}-${mes}-${dia}`;
        },

        atualizarData(campo, data) {
            if (!this.equipe || !data) {
                return;
            }

            const horaAtual =
                this.formatarHoraParaInput(
                    this.equipe[campo]
                ) || '00:00';

            /*
             * Atualiza diretamente o campo original.
             *
             * Não faça:
             *
             * this.dataFormatada = valor;
             *
             * pois isso chama o setter novamente.
             */
            this.equipe[campo] =
                `${data} ${horaAtual}:00`;
        },
        novoProfissional() {
            
            const id = this.equipe.PROFISSIONAL_ID;
            // se o id estiver null
            if (id == null)
                return '' 

            const profissional = this.profissionais.find(
                item => item.PROFISSIONAL_ID === id
            );

            //verifica se esse profissional já foi adicionado a lista
            const profissionalExiste = this.profissionaisSelecionados.find(
                item => item.PROFISSIONAL_ID === id
            )

            if(profissionalExiste){
                console.log('Profissional já adicionado a lista')
                return
            }

            if (profissional) {
                this.profissionaisSelecionados.push(
                    {
                        VEICULO_ID: this.equipe.VEICULO_ID,
                        EQUIPE_DATA: null,
                        EQUIPE_TURNO: this.equipe.EQUIPE_TURNO, 
                        EQUIPE_ATIVO: 1,
                        PROFISSIONAL_ID: profissional.PROFISSIONAL_ID,
                        profissional: profissional,
                    }
                );
            }
      
            // console.log(this.profissionaisSelecionados)
        },
        deletarProfissional(profissional) {

            let id = profissional.PROFISSIONAL_ID

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir a o profissional ${profissional.PROFISSIONAL_NOME} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            })

            const index = this.profissionaisSelecionados.findIndex(
            item => item.PROFISSIONAL_ID == id
            );

            if (index !== -1) {
                this.profissionaisSelecionados.splice(index, 1);
            }
        },
=======
        showModal: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getShowModal']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setShowModal', newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getFullScreen']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setFullScreen', newValue)
            }
        },
        equipe: {
            get() {
                return this.$store.getters['MdNovoEquipeModule/getEquipe']
            },
            set(newValue) {
                this.$store.dispatch('MdNovoEquipeModule/setEquipe', newValue)
            }
        },
        veiculos() {
            return this.$store.getters['VeiculoViewModule/getVeiculos']
        },
        profissionais() {
            return this.$store.getters['ProfissionalViewModule/getProfissionais']
        },
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            this.equipe = null
            this.showModal = false
        },
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.equipe.EQUIPE_ID === null ? 'POST' : 'PUT',
                url: this.equipe.EQUIPE_ID === null ? `${this.baseUrl}/equipe/inserir` : `${this.baseUrl}/equipe/alterar`,
                data: this.equipe
            }).then(r => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success').then(r => {
                    this.$store.dispatch('EquipeViewModule/search', this.msgId)
                })
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        }
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
    }
}
</script>

<<<<<<< HEAD
<style scoped>
input[type='date'],
input[type='time'] {
    width: 100%;
    min-height: 40px;
    padding: 8px;
    border: 1px solid #bdbdbd;
    border-radius: 4px;
    font-size: 14px;
}

label {
    display: block;
    margin-bottom: 6px;
    color: #555;
    font-size: 14px;
}
</style>
=======
<style scoped></style>
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
