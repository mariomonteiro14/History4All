<template>
    <table class="table table-striped table-hover" :class="datatableStyle">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" 
                    :style="'width:'+column.width+';'+'cursor:pointer;'">
                    <span v-if="column.order" @click="$emit('sort', column.name)" :class="sortKey === column.name ? (sortOrders[column.name] > 0 ? 'sorting_asc' : 'sorting_desc') : 'sorting'">
                        {{column.label}} <i class="fas fa-sort"></i>
                    </span> 
                    <span v-else >
                        {{column.label}}
                    </span> 
                </th>
            </tr>
        </thead>
        <slot></slot>
    </table>
</template>
<script>
    export default{
        props: ['columns', 'sortKey', 'sortOrders', 'datatableStyle']
    }
</script>