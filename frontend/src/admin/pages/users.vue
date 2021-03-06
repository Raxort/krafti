<template>
  <div>
    <app-table
      v-if="['users', 'users-create', 'users-edit-id'].includes($route.name)"
      ref="table"
      :url="url"
      :fields="fields"
      :filters="filters"
      :row-class="rowClass"
      :sort="sort"
      :dir="dir"
    >
      <template slot="actions">
        <b-button variant="secondary" :to="{name: 'users-create'}">
          <fa :icon="['fas', 'plus']" /> Добавить пользователя
        </b-button>
        <b-button variant="info" class="ml-2" :to="{name: 'users-roles'}">
          Управление группами <fa :icon="['fas', 'arrow-right']" />
        </b-button>
      </template>

      <template v-slot:cell(fullname)="row">
        <user-avatar :user="row.item" :truncate="200" :show-name="true" :add="row.item.role.title" />
      </template>
      <template v-slot:cell(email)="row">
        <div v-if="row.value">{{ row.value.toLowerCase() }}</div>
        <div v-if="row.item.instagram">
          <a :href="`https://www.instagram.com/${row.item.instagram}/`" target="_blank">
            @{{ row.item.instagram.toLowerCase() }}
          </a>
        </div>
      </template>
      <template v-slot:cell(actions)="row">
        <b-button size="sm" variant="outline-secondary" :to="{name: 'users-edit-id', params: {id: row.item.id}}">
          <fa :icon="['fas', 'edit']" />
        </b-button>
        <b-button v-if="row.item.active" size="sm" variant="outline-warning" @click.prevent="onDisable(row.item)">
          <fa :icon="['fas', 'power-off']" />
        </b-button>
        <b-button v-else size="sm" variant="outline-success" @click.prevent="onEnable(row.item)">
          <fa :icon="['fas', 'play']" />
        </b-button>
        <b-button v-if="!row.item.orders_count" size="sm" variant="outline-danger" @click.prevent="onDelete(row.item)">
          <fa :icon="['fas', 'times']" />
        </b-button>
      </template>
    </app-table>

    <nuxt-child />
  </div>
</template>

<script>
export default {
  name: 'AdminUsers',
  data() {
    return {
      url: 'admin/users',
      fields: [
        {key: 'id', label: 'Id', sortable: true},
        {key: 'fullname', label: 'ФИО', sortable: true},
        {key: 'email', label: 'Связь'},
        // {key: 'role.title', label: 'Группа'},
        {key: 'orders_count', label: 'Покупки', sortable: true},
        {key: 'referrals_count', label: 'Рефералы', sortable: true},
        {key: 'actions', label: 'Действия'},
      ],
      filters: {
        query: '',
        role_id: null,
        active: null,
        confirmed: null,
      },
      sort: 'id',
      dir: 'desc',
    }
  },
  methods: {
    rowClass(item) {
      return item && !item.active ? 'text-muted' : ''
    },
    async onDisable(item) {
      try {
        await this.$axios.patch(this.url, {id: item.id, active: false})
        this.$refs.table.refresh()
      } catch (e) {}
    },
    async onEnable(item) {
      try {
        await this.$axios.patch(this.url, {id: item.id, active: true})
        this.$refs.table.refresh()
      } catch (e) {}
    },
    onDelete(item) {
      this.$confirm('Вы уверены, что хотите удалить эту запись?', async () => {
        await this.$axios.delete(this.url, {params: {id: item.id}})
        this.$refs.table.refresh()
      })
    },
  },
  head() {
    return {
      title: 'Админка / Пользователи',
    }
  },
}
</script>
