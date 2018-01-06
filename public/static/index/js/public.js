/*
 * @Author: Benfei Cao 
 * @Date: 2017-12-20 11:27:30 
 * @Last Modified by: Benfei Cao
 * @Last Modified time: 2017-12-20 13:35:53
 */

//封装localStorage
var store = {
    save(key,value){
        //将json转为字符串
        localStorage.setItem(key,JSON.stringify(value));
    },
    fetch(key){
        //将本地字符串转为json 若本地没有则返回空
        return JSON.parse(localStorage.getItem(key)) || [];
    }
};