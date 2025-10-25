<template>
    <div>
    </div>
</template>


<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import { mapGetters } from "vuex";

export default {
    name: "Home",
    components: { TratarErroAjax },
    props: {
        usuario_logado: {
            type: Object,
        },
        alerta_analise: {
            type: Object,
        },
    },
    data() {
        return {

        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
        }),
    },
    mounted() {
        this.$store.dispatch('AuthModule/setUsuario', this.usuario_logado)
    },

}
</script>

<style scoped></style>
