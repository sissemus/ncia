import Vue from 'vue'
import Vuetify from 'vuetify/lib'

Vue.use(Vuetify)

const ambiente = document.querySelector('meta[name="ambiente"]')
const opts = ambiente && ambiente.content === 'producao'
    ? {
        theme: {
            themes: {
                light: { primary: '#37413A' },
                dark: { primary: '#37413A' },
            },
        },
    }
    : {}

export default new Vuetify(opts)
