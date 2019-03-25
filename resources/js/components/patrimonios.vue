<template>
    <div >
        <h3>Patrimónios / Pesquisa</h3>
        <br>
        <div class="container-fluid">
            <div id='example-3' @change="getPatrimonios()">
                <div class="row">
                    <h4>Ciclos:</h4>
                    <input type="checkbox" id="check1Ciclo" value="1º ciclo" v-model="tableData.ciclo">
                    <label for="check1Ciclo">1º ciclo</label>
                    <input type="checkbox" id="check2Ciclo" value="2º ciclo" v-model="tableData.ciclo">
                    <label for="check2Ciclo">2º ciclo</label>
                    <input type="checkbox" id="check3Ciclo" value="3º ciclo" v-model="tableData.ciclo">
                    <label for="check3Ciclo">3º ciclo</label>
                    <input type="checkbox" id="checkSec" value="secundário" v-model="tableData.ciclo">
                    <label for="checkSec">Secundário</label>
                </div>
                <div class="row">
                    <div class="control">
                        <select class="custom-select col-sm" v-model="tableData.length" @change="getPatrimonios()">
                            <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                        </select>
                    </div>

                    <div class="col-sm">
                        <input class="form-control" type="text" v-model="tableData.search" placeholder="Pesquisar por Name"
                               @input="getPatrimonios()">
                    </div>
                    <p>Distrito:</p>
                    <select v-model="tableData.distrito" @change="getPatrimonios()">
                        <option v-for="distrito in distritos" :value="distrito">
                            {{ distrito }}
                        </option>
                    </select>
                    <p>Época histórica:</p>
                    <select v-model="tableData.epoca" @change="getEpocas()">
                        <option v-for="epoca in epocas" :value="epoca">
                            {{ epoca }}
                        </option>
                    </select>
                </div>

            </div>

            <datatable :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders"
                       @sort="sortBy">
                <tbody>
                <tr v-for="patrimonio in patrimonios" :key="patrimonio.id">
                    <td>
                        <img v-if="patrimonio.imgagens[0]" :src="'/imgPatrimonio/' + patrimonio.imgagens[0].foto" class="rounded-circle border border-warning" width="25" height="25" >

                    </td>
                    <td>{{patrimonio.nome}}</td>
                    <td>{{patrimonio.distrito}}</td>
                    <td>{{patrimonio.epoca}}</td>
                    <td>{{patrimonio.ciclo}}</td>
                </tr>
                </tbody>
            </datatable>
            <pagination :pagination="pagination" @prev="getPatrimonios(pagination.prevPageUrl)"
                        @next="getPatrimonios(pagination.nextPageUrl)">
            </pagination>
        </div>
    </div>
</template>

<script>
    import Datatable from './datatable.vue';
    import Pagination from './pagination.vue';
    export default{
        components:{
            datatable: Datatable,
            pagination: Pagination,
        },
        created() {
            this.getPatrimonios();
            this.getDistritos();
        },
        data(){
            let sortOrders = {};
            let columns = [
                {width: '2%', label: 'Imagem', name: 'foto', order:false},
                {width: '20%', label: 'Nome', name: 'nome', order:true},
                {width: '7%', label: 'Distrito', name: 'distrito', order:true},
                {width: '7%', label: 'Époda Histórica', name: 'epoca', order:true},
                {width: '7%', label: 'Ciclo', name: 'ciclo', order:true},
            ];
            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return{
                alertSucces:{
                    show:false,
                    Message:'',
                },
                distritos: [],
                epocas: ['Todas', 'pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                patrimonios: [],
                columns: columns,
                sortKey: 'nome',
                sortOrders: sortOrders,
                perPage: ['5','10', '20', '30'],
                tableData: {
                    distrito: '',
                    epoca: '',
                    ciclo: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                    search: '',
                    draw: 0,
                    length: 5,
                    column: 0,
                    dir: 'desc',
                },
                pagination: {
                    lastPage: '',
                    currentPage: '',
                    total: '',
                    lastPageUrl: '',
                    nextPageUrl: '',
                    prevPageUrl: '',
                    from: '',
                    to: ''
                },
            }
        },

        methods:{
            getDistritos() {
                axios.get('api/patrimonios/distritos').then(response => {
                    this.distritos = (response.data);
                    this.distritos.unshift('Todos');//mete no inicio
                });
            },
            getPatrimonios(url = 'api/patrimonios') {
                this.tableData.draw++;
                axios.get(url, {params: this.tableData})
                    .then(response => {
                        let data = response.data;
                        if (this.tableData.draw == data.draw){
                            this.patrimonios = data.data.data;
                            this.configPagination(data.data);
                        }
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            configPagination(data) {
                this.pagination.lastPage = data.last_page;
                this.pagination.currentPage = data.current_page;
                this.pagination.total = data.total;
                this.pagination.lastPageUrl = data.last_page_url;
                this.pagination.nextPageUrl = data.next_page_url;
                this.pagination.prevPageUrl = data.prev_page_url;
                this.pagination.from = data.from;
                this.pagination.to = data.to;
            },
            sortBy(key) {
                this.sortKey = key;
                this.sortOrders[key] = this.sortOrders[key] * -1;
                this.tableData.column = this.getIndex(this.columns, 'name', key);
                this.tableData.dir = this.sortOrders[key] === 1 ? 'asc' : 'desc';
                this.getPatrimonios();
            },
            onDateSelected: function(date){
                this.tableData.date = date;
                this.getPatrimonios();
            },
            getIndex(array, key, value) {
                return array.findIndex(i => i[key] == value)
            },
            buildSuccessMessage(message){
                this.alertSucces.show = true;
                this.alertSucces.message = message;
                setTimeout(() => {
                    this.alertSucces.show = false;
                }, 2000);
            },
        },
    };
</script>