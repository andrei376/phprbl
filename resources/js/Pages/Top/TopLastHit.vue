<template>
    <div>
        <data-table
            :columns="columns"
            :table-name="tableName"
            :data-url="this.route('top.lasthit', { 'list': this.list})"
            :order-by="'hits.updated_at'"
            :order-dir="'desc'"
            :per-page-rows="10"
        />
    </div>
</template>

<script>

import DataTable from "@/Components/DataTable";

export default {
    name: "TopLastHit",
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
                    'class': 'w-5/12',
                    'sort': true,
                    'sortField': 'iplong',
                    'searchField': 'format_cidr',
                    'html': true
                },
                {
                    'name': this.__('Hit date'),
                    'showField': 'hit_date',
                    'class': 'w-4/12',
                    'sort': true,
                    'sortField': 'hits.updated_at',
                },
                {
                    'name': this.__('Hit count'),
                    'showField': 'count',
                    'class': 'w-2/12',
                    'sort': false,
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
