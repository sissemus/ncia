import moment from "moment";

export default {
    data() {
        return {
            TipoDocumentoEnum: {
                CPF: 2,
            },
            chartColors: {
                red: 'rgb(255, 99, 132)',
                orange: 'rgb(255, 159, 64)',
                yellow: 'rgb(255, 205, 86)',
                green: 'rgb(75, 192, 192)',
                blue: 'rgb(54, 162, 235)',
                purple: 'rgb(153, 102, 255)',
                grey: 'rgb(201, 203, 207)',
                deepOrange: 'rgb(255, 87, 34)',
                lightGreen: 'rgb(139,195,74)'
            },
            Situacao: {
                SEM_FILA: 1,
                POUCA_FILA: 2,
                FILA_MODERADA: 3,
                FILA_INTENSA: 4,
                ENCERRADO: 5,
            },
            LocalTipo: {
                CMV: 1,
                DRIVE_THRU: 2,
            },
        }
    },

    methods: {
        formatarDataSQL(value, formato = 'YYYY-MM-DD') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value).format(formato);
            }
        },
        formatarDataHora(value, formato = 'DD/MM/YYYY HH:mm') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value).format(formato);
            }
        },
        formatarDataBR(value, formato = 'DD/MM/YYYY') {
            if (value === '' || value === undefined || value === null) {
                return '';
            } else {
                return moment(value, 'YYYY-MM-DD').format(formato);
            }
        },
        incrementarData(value, incremento, tipo = 'days') {
            return moment(value, 'YYYY-MM-DD').add(incremento, tipo).format('YYYY-MM-DD')
        },
        calcularIdade(data) {
            return moment().diff(data,'years', false);
        },
        calcularTempoEspera(data) {
            return moment().diff(data,'days', false);
        },
        transparentize: function(color, opacity) {
            let alpha = opacity === undefined ? 0.2 : 1 - opacity;
            return Color(color).alpha(alpha).rgbString();
        },
        formatarMoedaBR(value){
            return 'R$ '+ parseFloat(value).toLocaleString('pt-br',{minimumFractionDigits:2});
        }
    },

}
