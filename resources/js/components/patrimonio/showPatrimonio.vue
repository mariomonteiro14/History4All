<template>
    <div>
        <br><br><br><br><br>
        <h4 class="display-1">{{patrimonio.nome}} - {{patrimonio.distrito}}</h4>

        <p v-html="patrimonio.descricao"></p>
    </div>

</template>
<script type="text/javascript">
    export default {
        props: ['id'],
        data: function(){
            return {
                patrimonio: {
                    id: '',
                    nome: '',
                    descricao: '',
                    distrito: '',
                    epoca: '',
                    ciclo: '',
                    images: []
                }
            };
        },
        methods: {
            getPatrimonio() {
                axios.get('/api/patrimonios/' + this.id)
                    .then(response => {
                        this.patrimonio = response.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            }
        },
        mounted() {
            this.getPatrimonio();
        }
    }
</script>