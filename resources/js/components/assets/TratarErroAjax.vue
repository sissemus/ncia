<template>
    <v-alert
        color="red"
        title="Erro"
        dark
        type="error"
        border="left"
        prominent
        dismissible
        :id="id"
        :value="show"
        @input="fecharAlert"
        style="border-radius: 0"
    >
    <h4>Oops..</h4>
    <p v-html="message"></p>
    </v-alert>
</template>

<script>
import {mapGetters, mapActions} from 'vuex';
export default {
    name: "TratarErroAjax",
    props: ['id'],
    created() {
        this.setarAlert({
            id: this.id,
            show: false,
            message: '',
        });
    },
    computed: {
        ...mapGetters({
            getAlert:   'TratarErroAjaxModule/getAlert',
        }),
        show() {
            //let talerts = this.$store.state['TratarErroAjaxModule'].show[this.id];
            let alerts = this.getAlert;
            let i = alerts.findIndex(r => r.id === this.id);
            return i >= 0 ? alerts[i].show : false;
        },
        message() {
            let alerts = this.getAlert;
            let i = alerts.findIndex(r => r.id === this.id);
            return i >= 0 ? alerts[i].message : "";
        }
    },
    methods: {
        ...mapActions({
            setAlert: 'TratarErroAjaxModule/setAlert',
            setFecharAlert: 'TratarErroAjaxModule/fecharAlert'
        }),
        setarAlert(payload) {
            this.setAlert(payload);
        },
        fecharAlert() {
            this.setFecharAlert(this.id);
        }
    }
}
</script>

<style scoped>
    *,*:focus,*:hover{
        outline:none;
    }
</style>
