function digitsOnly(e)
{

    var charCode = (e.which) ? e.which : event.keyCode
    
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {

        return false

    }

    return true
    
}
