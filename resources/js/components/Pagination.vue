<template>
  <ul class="container">
    <li class="button" @click.prevent="previousPage" :class="{disabled: isInFirstPage}">&lt;</li>
    <li class="button" @click.prevent="onClickPage(1)" :class="{disabled: isInFirstPage, active: isPageActive(1)}">1</li>
    <div class="fixed-gap"></div>
    <li v-for="page in pages" class="button" v-bind:key="page.id" @click="onClickPage(page.name)" :disabled="page.isDisabled" :class="{ active: isPageActive(page.name)}">
        {{ page.name }}
    </li>
    <div class="fixed-gap"></div>
    <li class="button" @click.prevent="onClickPage(this.totalPages)" :class="{disabled: isInLastPage, active: isPageActive(this.totalPages)}">{{this.totalPages}}</li>
    <li class="button" @click.prevent="nextPage" :class="{disabled: isInLastPage}">&gt;</li>
  </ul>
</template>
<script>
export default {
    data(){
        return {
            startPage: 1
        }
    },
    props: {
        totalPages: {
          type: Number,
          required: true
        },
        currentPage: {
          type: Number,
          required: true
        },
        nextVisibleAmount: {
          type: Number,
          required: false,
          default: 1
        }
    },  
  computed: {
    pages() {
      const range = [];
      for (let i = Math.min(this.currentPage - this.nextVisibleAmount, this.totalPages - this.nextVisibleAmount - 2);
       i <= Math.max(this.currentPage + this.nextVisibleAmount, this.startPage + this.nextVisibleAmount + 2); i++ ) {
          if(i <= this.startPage || i >= this.totalPages)
            continue;
          if(i != this.totalPages) {
            range.push({
              name: i,
              isDisabled: i === this.currentPage
            });
          }  
      }
      return range;
    },
    isInFirstPage() {
      return this.currentPage === this.startPage;
    },
    isInLastPage() {
      return this.currentPage === this.totalPages;
    },
  },
  methods: {
    previousPage() {
      this.$emit('pagechanged', this.currentPage - 1);
    },
    nextPage() {
      this.$emit('pagechanged', this.currentPage + 1);
    },
    onClickPage(page) {
      this.$emit('pagechanged', page);
    },
    isPageActive(page) {
      return this.currentPage === page;
    }
  }
}
</script>
<style>
.container {
  list-style-type: none;
  display: table;
  margin: 0 auto;
  padding: 3px;
}
.button {
  display: inline-block;
  border: 3px solid #c2c2c2;
  background-color: #adacac;
  padding: 15px;
  padding-top: 7.5px;
  padding-bottom: 7.5px;
  cursor: pointer;
  font-size: 20px;
}
.fixed-gap{
  display: inline-block;
  margin-left: 5px;
  margin-right: 5px; 
}
.button:hover{
    background-color: #d3d3d3;
}
.active, .active:hover {
  background-color: #176a94;
  color: #d6d5d5;
  pointer-events: none;
}
.disabled{
    pointer-events: none;
}
</style>