export default{
    data(){
        return{
            sort: true,
        }
    },
    methods:{
        order_icon(coluna='',obj={}){
            let icon = 'mdi-swap-vertical'

            if(obj.orderBy == coluna){
               icon = this.sort?'mdi-arrow-up':'mdi-arrow-down';
            }
            return icon;
        },
        ordenar(coluna=null,obj={},method=null){
            this.sort=!this.sort;
            obj.orderBy = coluna;
            obj.sort = this.sort?'asc':'desc';
            if(method)
            method();
        },
    }
}