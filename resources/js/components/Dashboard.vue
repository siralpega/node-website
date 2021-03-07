<template>
  <div>
    <div class="header">
        <div class="flex-3 row ml-2 mt-2">
          <img src="../../assets/logo.png" width="128" height="128" />
          <h1 class="text-large text-blue">Node Dashboard</h1>
        </div>

        <div class="header-totals flex-6">
          <div class="box flex-5" :style="'background-color:' + getDotColor(ramInUse, totalRam)">
              <div class="flex-3 ml-1" style="margin: auto;">
                <h3 class="text-large text-white">TOTAL RAM</h3>
              </div>
               <div class="flex-6" style="margin: auto;">
                <h3 class="text-medium text-white">
                  {{ramInUse}} / {{totalRam}}MB
                </h3>
              </div>
          </div>
          <div class="box flex-5" :style="'background-color:' + getDotColor(diskInUse, totalDisk)">
              <div class="flex-3 ml-1" style="margin: auto;">
                <h3 class="text-large text-white">TOTAL DISK</h3>
              </div>
              <div class="flex-6" style="margin: auto;">
                <h3 class="text-medium text-white">
                  {{diskInUse}} / {{totalDisk}}MB
                </h3>
              </div>
          </div>   
      </div>

    </div>

    <div class="header-list">
      <div class="row">
        <div class="section flex-1">
           <h1 class="text-large text-white">ID</h1>
        </div>
        <div class="section flex-2">
           <h3 class="text-large text-white text-left">UP FOR</h3>
        </div>
        <div class="section flex-3">
          <div class="flex-7">
            <h3 class="text-large text-white ml-1">RAM</h3>
          </div>
        </div>
        <div class="section flex-3">
          <div class="flex-7">
            <h3 class="text-large text-white ml-1">DISK</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Node List -->
    <div class="list">
      <div class="node" v-for="node in pageInfo" v-bind:key="node.id" @click="toggleModal(node.id)">
        <div class="section flex-1">
          <h1 class="text-large text-white">{{ node.id }}</h1>
        </div>

        <div class="section flex-2">
            <h3 class="text-large text-white text-left">{{lastDowntime(node.up_since)}}</h3>
        </div>

        <div class="section row flex-3">
          <div class="flex-1">
            <span class="status-dot" :style="'background-color:' + getDotColor(node.allocated_ram,node.allocated_ram + node.free_ram)"></span>
          </div>
          <div class="flex-8"> 
            <h3 class="text-medium text-white text-left">
              {{ node.allocated_ram }} /
              {{ node.allocated_ram + node.free_ram }}MB
            </h3>
            
          </div>
        </div>

        <div class="section row flex-3">
          <div class="flex-1">
            <span class="status-dot" :style="'background-color:' + getDotColor(node.allocated_disk,node.allocated_disk + node.free_disk)"></span>
          </div>
          <div class="flex-8">
            <h3 class="text-medium text-white text-left">
              {{ node.allocated_disk }} /
              {{ node.allocated_disk + node.free_disk }}MB<!-- MB or GB? -->
            </h3>
          </div>
        </div>

      </div>

      <div class="row page-select">
          <Pagination v-on:pagechanged="changePage($event)" :totalPages="getTotalPages" :currentPage="this.page"></Pagination>
          <h3 class="text-medium text-white text-right">NODES:  {{ totalNodes }}</h3>
      </div>

    </div>

    <!-- Expanded Information Modal -->
    <modal v-if="showModal" @close="toggleModal()">
      <template v-slot:header>
        <div class="row">
          <div class="flex-2">
            <h3 class="text-large text-white">NODE {{getNode(modalNode).id}}</h3>
          </div>
          <div class="flex-5 center">
            <div class="row center">
              <h1 class="text-medium text-blue">UP SINCE:</h1>
              <h3 class="text-small text-white ml-1">{{convertToTimestamp(getNode(modalNode).up_since)}}</h3>
            </div>
          </div>
          <div class="flex-3">
            <div class="row">
              <h3 class="text-medium text-blue text-left">Difference Since</h3>
              <select class="ml-1" @change="timeSelected = true" v-model="selectedTimestamp">
                <option v-for="timestamp in getNode(modalNode).history" v-bind:key="timestamp.id">{{timestamp.id}}</option>
              </select>
            </div>
          </div>
        </div>
      </template>
      <template v-slot:body>
        <div class="row">

          <div class="flex-2">
            <div>
              <h3 class="text-large text-white">CURRENT RAM</h3>
              <div class="row">
                <div class="flex-5">
                  <h3 class="text-small text-blue text-left">ALLOCATED: </h3>
                  <h3 class="text-small text-blue text-left">FREE: </h3>
                  <h3 class="text-small text-blue text-left">TOTAL: </h3>
                </div>
                <div class="flex-5">
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).allocated_ram}}MB ({{getUsagePercent(getNode(modalNode).allocated_ram, getNode(modalNode).free_ram)}}%)</h3>
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).free_ram}}MB ({{getUsagePercent(getNode(modalNode).free_ram, getNode(modalNode).allocated_ram)}}%)</h3>
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).allocated_ram + getNode(modalNode).free_ram}}MB</h3>
                </div>
              </div>
            </div>
            <div>
              <h3 class="text-large text-white">CURRENT DISK</h3>
              <div class="row">
                <div class="flex-5">
                  <h3 class="text-small text-blue text-left">ALLOCATED: </h3>
                  <h3 class="text-small text-blue text-left">FREE: </h3>
                  <h3 class="text-small text-blue text-left">TOTAL: </h3>
                </div>
                <div class="flex-5">
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).allocated_disk}}MB ({{getUsagePercent(getNode(modalNode).allocated_disk, getNode(modalNode).free_disk)}}%)</h3>
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).free_disk}}MB ({{getUsagePercent(getNode(modalNode).free_disk, getNode(modalNode).allocated_disk)}}%)</h3>
                  <h3 class="text-small text-white text-left">{{getNode(modalNode).allocated_disk + getNode(modalNode).free_disk}}MB</h3>
                </div>
              </div>
            </div>
          </div>

          <div class="flex-2" style="padding-left: 5px; padding-right: 5px;">
            <div>
              <h3 class="text-large text-white">SELECTED RAM</h3>
                <div class="row">
                  <div class="flex-5" v-if="timeSelected">
                    <h3 class="text-small text-blue text-left">ALLOCATED: </h3>
                    <h3 class="text-small text-blue text-left">FREE: </h3>
                    <h3 class="text-small text-blue text-left">TOTAL: </h3>
                  </div>
                  <div class="flex-5" v-if="timeSelected">
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).allocated_ram}}MB 
                      ({{getChangeInTimestamp(getNode(modalNode).allocated_ram, getTimestampFromNode(modalNode, selectedTimestamp).allocated_ram)}}%)
                    </h3>
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).free_ram}}MB 
                      ({{getChangeInTimestamp(getNode(modalNode).free_ram, getTimestampFromNode(modalNode, selectedTimestamp).free_ram)}}%)
                    </h3>
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).allocated_ram 
                                                                + getTimestampFromNode(modalNode, selectedTimestamp).free_ram}}MB</h3>
                  </div>
                  <div v-if="!timeSelected">
                     <h3 class="text-small text-white text-left">Select a timestamp on the top right</h3>
                     <div class="mt-4"></div>
                  </div>
              </div>
            </div>
            <div>
              <h3 class="text-large text-white">SELECTED DISK</h3>
                <div class="row">
                  <div class="flex-5" v-if="timeSelected">
                    <h3 class="text-small text-blue text-left">ALLOCATED: </h3>
                    <h3 class="text-small text-blue text-left">FREE: </h3>
                    <h3 class="text-small text-blue text-left">TOTAL: </h3>
                  </div>
                  <div class="flex-5" v-if="timeSelected">
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).allocated_disk}}MB 
                      ({{getChangeInTimestamp(getNode(modalNode).allocated_disk, getTimestampFromNode(modalNode, selectedTimestamp).allocated_disk)}}%)</h3>
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).free_disk}}MB 
                      ({{getChangeInTimestamp(getNode(modalNode).free_disk, getTimestampFromNode(modalNode, selectedTimestamp).free_disk)}}%)
                    </h3>
                    <h3 class="text-small text-white text-left">{{getTimestampFromNode(modalNode, selectedTimestamp).allocated_disk + getTimestampFromNode(modalNode, selectedTimestamp).free_disk}}MB</h3>
                  </div>
                  <div v-if="!timeSelected">
                     <h3 class="text-small text-white text-left">Select a timestamp on the top right</h3>
                     <div class="mt-4"></div>
                  </div>
              </div>
            </div>
          </div>
          <div class="flex-6">
            <apexchart height="95%" type="line" :options="options" :series="getTimestampFromNodeForGraph(modalNode, selectedTimestamp)"></apexchart>
          </div>
        </div>

      </template>
  </modal>

  </div>
</template>

<script>
import axios from "axios";

import { db } from "../app";
import { TIMESTAMP } from "../app";
import Pagination from './Pagination.vue';
import Modal from './Modal.vue';

export default {
  components: { Pagination, Modal },
  props: {},
  data() {
    return {
      info: [],
      pageInfo: [],
      page: 1,
      nodesPerPage: 10,
      totalNodes: 0,
      ramInUse: 0,
      diskInUse: 0,
      totalRam: 0,
      totalDisk: 0,
      showModal: false,
      modalNode: 1,
      timeSelected: false,
      selectedTimestamp: null,
      options: {
        xaxis: {
          type: 'datetime',
          labels: {
            style:{
              colors: '#fff',
              fontSize: '12px',
              fontFamily: 'Helvetica, Arial, sans-serif',
              fontWeight: 400,
            },
            datetimeFormatter: {
              year: 'yyyy',
              month: "MMM 'yy",
              day: 'dd MMM',
              hour: 'HH:mm',
            },
            format: 'dd/MM HH:mm'
          },
        },
        yaxis: {
          labels: {
            style:{
              colors: '#fff',
              fontSize: '12px',
              fontFamily: 'Helvetica, Arial, sans-serif',
              fontWeight: 400,
            },
            datetimeFormatter: {
              year: 'yyyy',
              month: "MMM 'yy",
              day: 'dd MMM',
              hour: 'HH:mm',
            }
          },
          title: {
             text: 'GB',
             style:{
              color: '#fff',
              fontSize: '12px',
              fontFamily: 'Helvetica, Arial, sans-serif',
              fontWeight: 400,
            }
          }
        },
        colors:['#d65c5b', '#8e2423', '#8ddb42', '#4d811b'],
        grid: {
          row: {
            colors: ['#ebebeb']
          }
        },
        legend: {
          labels: {
            colors:['#d6d5d5'],
          }
        },
        tooltip: {
          enabled: true,
          x: {
            show:true,
            format: 'dd/MM HH:mm'
          }
        },
      },
    };
  },
  created() {},
  computed: {
    getTotalPages(){
      return this.totalNodes / this.nodesPerPage;
    }
  },
  watch: {},
  methods: {
    /* Updating the Page */
    changePage(num){
    //  console.log('Switching to page ' + this.page);
      this.page = num;
      this.getDataForPage();
    },
    getDataForPage()
    {
      var end = this.page * this.nodesPerPage;
      var start = end - this.nodesPerPage;
      var nodes = [];
      for(var i = start; i < end; i++){
        if(this.info[i] != null)
             nodes[i - start] = this.info[i];
      }
      this.pageInfo = nodes;
    },
    /* DATABASE */
    //Alternative to current method, where we get all data at once, this will only get some
    getDataFromDatabaseForPage()
    {
      var end = (this.page * this.nodesPerPage) - 1;
      var start = (end - this.nodesPerPage) + 1;
      axios.get("api/data/" + start + "/" + end).then((response) => {
        var data = [], history = [];
        for(var i = 0; i < response.data.length; i++){
          var node = response.data[i];
          var mostRecentDate, ts;
          if(node.timestamps != undefined)
          {
            for(var timestamp in node.timestamps){
                var date = new Date(timestamp);
                if(mostRecentDate == null || date > mostRecentDate){
                    mostRecentDate = date;
                    ts = timestamp;
                  }
                  history.push({
                      allocated_ram: node.timestamps[timestamp].allocated_ram,
                      allocated_disk: node.timestamps[timestamp].allocated_disk,
                      free_ram: node.timestamps[timestamp].free_ram,
                      free_disk: node.timestamps[timestamp].free_disk,
                      id: timestamp,
                      timestamp: TIMESTAMP
                  });
            }
            data.push({
              allocated_ram: node.timestamps[ts].allocated_ram,
              allocated_disk: node.timestamps[ts].allocated_disk,
              free_ram: node.timestamps[ts].free_ram,
              free_disk: node.timestamps[ts].free_disk,
              id: node.id,
              up_since: node.up_since,
              history: history
            });
          }
          history = [];
        }
        this.info = data;
        this.totalNodes = data.length;
        this.changePage(1);
        this.calcAndUpdateTotals();
      });

    },


    getDataFromDatabase(){
      console.log('Retrieving from database.');
      axios.get("api/data").then((response) => {
        var data = [], history = [];
        for(var i = 0; i < response.data.length; i++){
          var node = response.data[i];
          var mostRecentDate, ts;
          if(node.timestamps != undefined)
          {
            for(var timestamp in node.timestamps){
                var date = new Date(timestamp);
                if(mostRecentDate == null || date > mostRecentDate){
                    mostRecentDate = date;
                    ts = timestamp;
                  }
                  history.push({
                      allocated_ram: node.timestamps[timestamp].allocated_ram,
                      allocated_disk: node.timestamps[timestamp].allocated_disk,
                      free_ram: node.timestamps[timestamp].free_ram,
                      free_disk: node.timestamps[timestamp].free_disk,
                      id: timestamp,
                      timestamp: TIMESTAMP
                  });
            }
            data.push({
              allocated_ram: node.timestamps[ts].allocated_ram,
              allocated_disk: node.timestamps[ts].allocated_disk,
              free_ram: node.timestamps[ts].free_ram,
              free_disk: node.timestamps[ts].free_disk,
              id: node.id,
              up_since: node.up_since,
              history: history
            });
          }
          history = [];
        }
        this.info = data;
        this.totalNodes = data.length;
        this.changePage(1);
        this.calcAndUpdateTotals();
      });  
    },
    /* */
    calcAndUpdateTotals(){
      var allocatedRam = 0, totalRam = 0, allocatedDisk = 0, totalDisk = 0;
      for(var i = 0; i < this.info.length; i++) {
        allocatedRam += this.info[i].allocated_ram;
        totalRam += (this.info[i].allocated_ram + this.info[i].free_ram);
        allocatedDisk += this.info[i].allocated_disk;
        totalDisk += (this.info[i].allocated_disk + this.info[i].free_disk);
      //  console.log('Node ' + (i + 1) + ': ' + response.data[i].allocated_ram + '/' + (response.data[i].allocated_ram + response.data[i].free_ram));
      }
      this.ramInUse = allocatedRam;
      this.totalRam = totalRam;
      this.diskInUse = allocatedDisk;
      this.totalDisk = totalDisk;
    },
    convertToTimestamp(time) {
      var date  = new Date(time);
      return date + '';
    },
    lastDowntime(timestamp){
      var date = new Date(timestamp);
      var diff = Math.abs(parseInt((date-Date.now())/(24*3600*1000)));
      if(diff < 0)
        diff = 0;
      if(diff < 1)
        return diff + ' hours';
      else
        return diff + ' days'
    },
    getNode(id){
      if(id >= 1 && id < this.totalNodes)
        return this.info[id - 1]
      else
        return null;
    },
    getTimestampFromNode(id, timestamp){
      var node = this.getNode(id);
      if(node == null)
        return;
      for(var time in node.history){
        if(node.history[time].id == timestamp)
          return node.history[time];
      }
      return null;
    },
    getTimestampFromNodeForGraph(id, timestamp){
        var node = this.getNode(id);
        if(node == null)
        {
          console.error('Could not find a node with the id of ' + id);
          return null;
        }
        if(timestamp == null)
          return;
        var history_allocated_disk = [], history_allocated_ram = [], history_free_ram = [], history_free_disk = [];
        var timestampCutoff = new Date(timestamp);
        timestampCutoff = new Date(Date.UTC(timestampCutoff.getFullYear(), timestampCutoff.getMonth(), timestampCutoff.getDate(), timestampCutoff.getHours(), timestampCutoff.getMinutes(), timestampCutoff.getSeconds()));
       
        for(var time in node.history){
          var date = new Date(node.history[time].id);
          date = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds()));
        
          if(date < timestampCutoff || date.getMinutes() != 0)
              continue;

          var df = date.getTime();
          history_allocated_ram.push([df, (node.history[time].allocated_ram / 1000).toFixed(1)]);
          history_free_ram.push([df, (node.history[time].free_ram / 1000).toFixed(1)]);
          history_allocated_disk.push([df, (node.history[time].allocated_disk / 1000).toFixed(1)]);
          history_free_disk.push([df, (node.history[time].free_disk / 1000).toFixed(1)]);
        }  
        var series = [{
            name: 'Allocated RAM',
            data:  history_allocated_ram
          },{
            name: 'Free RAM',
            data: history_free_ram
          },{
            name: 'Allocated Disk',
            data: history_allocated_disk
          },{
            name: 'Free Disk',
            data: history_free_disk
          },
          ];
          return series;
    },
    getChangeInTimestamp(current, history){
      var result = (((history / current) - 1) * 100).toFixed(1);
      return (result < 0) ? result : '+' + result;
    },
    getUsagePercent(divisor, factor){
      var result = (divisor / (divisor + factor)) * 100;
      return result.toFixed(1) + '';
    },
    toggleModal(id) {
      this.showModal = !this.showModal;
      if(this.showModal)
        this.modalNode = id;
    },
    getDotColor(div, total) {
      var perc = div / total;
      if (perc < 0.75) return "#16d116";
      else if (perc < 0.9) return "yellow";
      else return "red";
    }
  },
  mounted() {
 this.getDataFromDatabase();
// this.getDataFromDatabaseForPage();
  },
};
</script>
