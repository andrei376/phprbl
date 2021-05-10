<template>
    <div>
        <data-table
            :columns="columns"
            :table-name="tableName"
            :data-url="this.route('top.hits', { 'list': this.list})"
            :order-by="'hits_sum_count'"
            :order-dir="'desc'"
            :per-page-rows="10"
        />
    </div>
</template>

<script>

import DataTable from "@/Components/DataTable";

export default {
    name: "TopHits",
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
                    'name': this.__('IP'),
                    'showField': 'format_cidr',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'format_cidr',
                    'searchField': 'format_cidr'
                },
                {
                    'name': this.__('netname'),
                    'showField': 'netname',
                    'class': 'w-7/12',
                    'sort': true,
                    'sortField': 'netname',
                    'searchField': 'netname'
                },
                {
                    'name': this.__('country'),
                    'showField': 'country',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'country',
                    'searchField': 'country'
                },
                {
                    'name': this.__('Total hits'),
                    'showField': 'hits_sum_count_format',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'hits_sum_count',
                },
                {
                    'name': this.__('Total IP'),
                    'showField': 'total_ip_format',
                    'class': 'w-1/12',
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
