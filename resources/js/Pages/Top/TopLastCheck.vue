<template>
    <div>
        <data-table
            :columns="columns"
            :table-name="tableName"
            :data-url="this.route('top.lastcheck', { 'list': this.list})"
            :order-by="'total_ip'"
            :order-dir="'desc'"
            :per-page-rows="10"
        />
    </div>
</template>

<script>

import DataTable from "@/Components/DataTable";

export default {
    name: "TopLastCheck",
    components: {
        DataTable
    },
    props: [
        'list'
    ],
    data() {
        return {
            columns: [
                {
                    'name': this.__('index'),
                    'showField': 'index',
                    'sort': false,
                    'class': 'w-1/12'
                },
                {
                    'name': this.__('Last check'),
                    'showField': 'top_last_check',
                    'class': 'w-6/12',
                    'sort': true,
                    'sortField': 'top_last_check',
                    'searchField': 'top_last_check'
                },
                {
                    'name': this.__('DB Rows'),
                    'showField': 'row_count_format',
                    'class': 'w-2/12',
                    'sort': true,
                    'sortField': 'row_count',
                },
                {
                    'name': this.__('Total IP'),
                    'showField': 'total_ip_format',
                    'sort': true,
                    'sortField': 'total_ip'
                }
            ]
        }
    },
    computed: {
        tableName() {
            return this.__('List')+' '+this.list;
        }
    }
}
</script>
