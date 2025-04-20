<template>
    <div>
        <table class="table table-bordered table-striped table-sm">
            <tbody>
                <tr>
                    <td class="w-6/12">Memcache Server version:</td>
                    <td class="w-6/12">{{ stats["version"] }}</td>
                </tr>
                <tr><td>Process id of this server process </td><td>{{ stats["pid"] }}</td></tr>
                <tr><td>Number of seconds this server has been running </td><td>{{ parseInt((stats["uptime"]/3600)/24) }} days ({{ stats['uptime'] }} seconds)</td></tr>
                <tr><td>Total number of items stored by this server ever since it started </td><td>{{ stats["total_items"] }}</td></tr>
                <tr><td>Number of open connections </td><td>{{ stats["curr_connections"] }}</td></tr>
                <tr><td>Total number of connections opened since the server started running </td><td>{{ stats["total_connections"] }}</td></tr>
                <tr><td>Number of connection structures allocated by the server </td><td>{{ stats["connection_structures"] }}</td></tr>
                <tr><td>Cumulative number of retrieval requests </td><td>{{ stats["cmd_get"] }}</td></tr>
                <tr><td> Cumulative number of storage requests </td><td>{{ stats["cmd_set"] }}</td></tr>
                <tr><td>Number of keys that have been requested and found present </td><td>{{ stats["get_hits"] }}  <span class="badge bg-success ml-3">{{ percCacheHit }}% found</span></td></tr>
                <tr><td>Number of items that have been requested and not found </td><td>{{ stats["get_misses"] }}  <span class="badge bg-danger ml-3">{{ percCacheMiss }}% not found</span></td></tr>
                <tr><td>Total number of bytes read by this server from network </td><td>{{ MBRead }} Mega Bytes ({{ stats['bytes_read'] }})</td></tr>
                <tr><td>Total number of bytes sent by this server to network </td><td>{{ MBWrite }} Mega Bytes ({{ stats['bytes_written'] }})</td></tr>
                <tr><td>Number of bytes this server is allowed to use for storage.</td><td>{{ MBSize }} Mega Bytes</td></tr>
                <tr><td>Number of valid items removed from cache to free memory for new items.</td><td><span class="badge bg-danger">{{ stats["evictions"] }}</span></td></tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: "MemcacheStats",
    props: [
        'stats'
    ],
    computed: {
        percCacheHit: function() {
            let value = this.stats['get_hits'] / this.stats['cmd_get'] * 100;
            value = value.toFixed(2);

            return value;
        },
        percCacheMiss: function() {
            let value = 100 - this.percCacheHit;
            value = value.toFixed(2);

            return value;
        },
        MBRead: function() {
            let value = this.stats["bytes_read"] / (1024 * 1024);
            value = value.toFixed(2);

            return value;
        },
        MBWrite: function() {
            let value = this.stats["bytes_written"] / (1024 * 1024);
            value = value.toFixed(2);

            return value;
        },
        MBSize: function() {
            let value = this.stats["limit_maxbytes"] / (1024 * 1024);
            value = value.toFixed(2);

            return value;
        }
    }
}
</script>
