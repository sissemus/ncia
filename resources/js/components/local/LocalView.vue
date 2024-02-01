<template>
    <div>
        <v-card class="mb-5">
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Meus locais</v-toolbar-title>
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
                            v-model="usuarioLocal"
                            return-object
                            @change="onchangeUsuarioLocal"
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
            </v-card-text>
        </v-card>

        <v-card class="mb-5">
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Detalhes do local</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn fab elevation="0" small color="green" dark title="Novo local" @click="novoLocal"><v-icon>mdi-plus</v-icon></v-btn>
            </v-toolbar>
            <v-card-text>
                <v-row>
                    <v-col>
                        <label>Descrição</label>
                        <v-text-field
                            label="Descrição"
                            solo
                            hide-details
                            v-model="usuarioLocal['local']['LOCAL_DESCRICAO']"
                        ></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col>
                        <label>Endereço</label>
                        <v-text-field
                            label="Endereço"
                            solo
                            hide-details
                            v-model="usuarioLocal['local']['LOCAL_ENDERECO']"
                        ></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col>
                        <label>Abertura</label>
                        <v-text-field
                            label="Abertura"
                            solo
                            hide-details
                            type="time"
                            v-model="usuarioLocal['local']['LOCAL_ABERTURA']"
                        ></v-text-field>
                    </v-col>
                    <v-col>
                        <label>Fechamento</label>
                        <v-text-field
                            label="Fechamento"
                            solo
                            hide-details
                            type="time"
                            v-model="usuarioLocal['local']['LOCAL_FECHAMENTO']"
                        ></v-text-field>
                    </v-col>
                    <v-col>
                        <label>Tipo de Local</label>
                        <v-select
                            label="Tipo de Local"
                            solo
                            hide-details
                            item-text="text"
                            item-value="id"
                            :items="localTipo"
                            v-model="usuarioLocal['local']['LOCAL_TIPO']"
                        ></v-select>
                    </v-col>
                    <v-col>
                        <label>Ativo</label>
                        <v-select
                            label="Ativo"
                            solo
                            hide-details
                            item-text="text"
                            item-value="id"
                            :items="localAtivo"
                            v-model="usuarioLocal['local']['LOCAL_ATIVO']"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

    </div>


</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import UsuarioLocal from "../../store/modules/payloads/UsuarioLocal.json"
import Publico from "../../store/modules/payloads/Publico.json"
import {mapGetters} from "vuex"
import Swal from "sweetalert2";
import UtilsMixins from "../../mixins/UtilsMixins";
export default {
    name: "LocalView",
    components: {TratarErroAjax},
    mixins: [UtilsMixins],
    props: {
        usuarioLocais: {
            type: Array
        }
    },
    mounted() {
        // console.log(this.usuarioLocais)
    },
    data() {
        return {
            msgId: "msgLocalView",
            msgIdDebug: "msgLocalViewDebug",
            usuarioLocal: JSON.parse(JSON.stringify(UsuarioLocal)),
            publicoAlvos: [],
            publico: JSON.parse(JSON.stringify(Publico)),
            localTipo: [
                {id: 1, text: "CMV"},
                {id: 2, text: "DRIVE_THRU"}
            ],
            localAtivo: [
                {id: 0, text: "Não"},
                {id: 1, text: "Sim"}
            ],
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        })
    },
    methods: {
        onchangeUsuarioLocal() {
            // this.listarPublicoAlvo()
        },
        listarPublicoAlvo() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/publico/listar/local`,
                params: {
                    localId: this.usuarioLocal['local']['LOCAL_ID']
                }
            }).then(r => {
                console.log(r.data)
                document.getElementById(this.msgIdDebug).innerHTML = r.data
                this.publicoAlvos = JSON.parse(JSON.stringify(r.data))
                return 0
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        },
        novoLocal() {
            this.usuarioLocal = JSON.parse(JSON.stringify(UsuarioLocal))
            this.publico = JSON.parse(JSON.stringify(Publico))
        },
        salvar() {
            let data = {
                local: this.usuarioLocal['local'],
                publico: this.publico
            }
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.usuarioLocal['local']['LOCAL_ID'] === null ? 'POST' : 'PUT',
                url: this.usuarioLocal['local']['LOCAL_ID'] === null ? `${this.baseUrl}/local/create` : `${this.baseUrl}/local/update`,
                data
            }).then(r => {
                this.usuarioLocal = JSON.parse(JSON.stringify(UsuarioLocal))
                this.publico = JSON.parse(JSON.stringify(Publico))
                Swal.fire("Sucesso", "Salvo com sucesso", "success")
                window.location.reload(true);
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
