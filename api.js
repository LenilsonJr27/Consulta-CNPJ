const URL = '';

async function chamarApi() {
    const res = await fetch(URL);
    if(res.status === 200){
        const obj = await res.json();
        console.log(obj);
    }
}

chamarApi();