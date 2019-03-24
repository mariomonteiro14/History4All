<template>
    <div >
        <h3>Patrimónios</h3>
        <div class="container-fluid">
            <div class="row">
                <div class="control">
                    <select class="custom-select col-sm" v-model="tableData.length" @change="getPatrimonios()">
                        <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm">
                        <input class="form-control" type="text" v-model="tableData.search" placeholder="Search Per Name"
                               @input="getPatrimonios()">
                    </div>
                    <div id='example-3' @change="getPatrimonios()">
                        <input type="checkbox" id="check1Ciclo" value="1" v-model="tableData.ciclo" >
                        <label for="check1Ciclo">1º ciclo</label>
                        <input type="checkbox" id="check2Ciclo" value="2" v-model="tableData.ciclo">
                        <label for="check2Ciclo">2º ciclo</label>
                        <input type="checkbox" id="check3Ciclo" value="3" v-model="tableData.ciclo">
                        <label for="check3Ciclo">3º ciclo</label>
                        <input type="checkbox" id="checkSec" value="sec" v-model="tableData.ciclo">
                        <label for="checkSec">Secundário</label>
                    </div>
                </div>
            </div>

            <datatable :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders"
                       @sort="sortBy">
                <tbody>
                <tr v-for="patrimonio in patrimonios" :key="patrimonio.id">
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
        },
        data(){
            let sortOrders = {};
            let columns = [
                {width: '7%', label: 'Nome', name: 'nome', order:true},
                {width: '7%', label: 'Distrito', name: 'distrito', order:true},
                {width: '20%', label: 'Époda Histórica', name: 'epoca', order:true},
                {width: '20%', label: 'Ciclo', name: 'ciclo', order:true},
            ];
            columns.forEach((column) => {
                sortOrders[column.name] = -1;
            });
            return{
                alertSucces:{
                    show:false,
                    Message:'',
                },
                patrimonios: [],
                columns: columns,
                sortKey: 'name',
                sortOrders: sortOrders,
                perPage: ['5','10', '20', '30'],
                tableData: {
                    date:'',
                    ciclo: ["1","2","3","sec"],
                    draw: 0,
                    length: 5,
                    search: '',
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
            getPatrimonios(url = 'api/patrimonios') {
                this.tableData.draw++;
                axios.get(url, {params: this.tableData})
                    .then(response => {
                        let data = response.data;
                        if (this.tableData.draw == data.draw) {
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