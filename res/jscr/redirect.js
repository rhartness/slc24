function redirect(url, x)
{    
    x--;
    
    document.getElementById("counter").innerHTML = x;
    
    if(x <= 0)
        document.location.href = url;
    else
        setTimeout("redirect(\""+url+"\", "+x+")", 1000);
}