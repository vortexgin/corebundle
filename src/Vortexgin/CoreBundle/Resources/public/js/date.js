/**
 * Created by dailysocial on 18/04/17.
 * Data prototype collection
 * Author Tommy Dian P
 */

var CustomDate = {
    _listMonth: [
        'Januari', 
        'Februari', 
        'Maret', 
        'April', 
        'Mei', 
        'Juni', 
        'Juli', 
        'Agustus', 
        'September', 
        'Oktober', 
        'November', 
        'Desember', 
    ], 
    getMonth(month) {
        var month = month || 0;
        return CustomDate._listMonth[month];
    }
};
Date.prototype.customFormat = function (format) {
    var format = format || 'Y-m-d',
        time = this.getFullYear() +
        '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
        '-' + ((this.getDate() < 10) ? '0' : '') + this.getDate();
    if (format == 'd-m-Y') {
        time = ((this.getDate() < 10) ? '0' : '') + this.getDate() +
            '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-' + this.getFullYear();
    } else if (format == 'Y-m-d G:i:s') {
        time = this.getFullYear() + '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-' + ((this.getDate() < 10) ? '0' : '') + this.getDate() + 
            ' ' + ((this.getHours() < 10) ? '0' : '') + this.getHours() + 
            ':' + ((this.getMinutes() < 10) ? '0' : '') + this.getMinutes() + 
            ':' + ((this.getSeconds() < 10) ? '0' : '') + this.getSeconds();
    } else if (format == 'dayofmonth') {
        time = this.getFullYear() +
            '-' + ((this.getMonth() + 1 < 10) ? '0' : '') + (this.getMonth() + 1) +
            '-01';
    }

    return time;
}