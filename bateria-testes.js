import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    vus: 10,          
    duration: '30s',  
};

// Esta parte roda uma vez para cada um dos 10 usuários (VU) no início
export function setup() {
    const loginUrl = 'http://auth-service:8000/api/login';
    const loginPayload = JSON.stringify({
        email: 'sender@k6.com',
        password: 'secret',
    });

    const loginRes = http.post(loginUrl, loginPayload, {
        headers: { 'Content-Type': 'application/json' },
    });

    const token = loginRes.json('token');
    
    if (!token) {
        console.error("FALHA NO LOGIN DO K6: Verifique se o auth-service está ON e o seeder rodou.");
    }

    return { token: token };
}

export default function (data) {
    const url = 'http://chat-service:8001/api/messages';
    
    const payload = JSON.stringify({
        receiver_id: 2,
        content: `Mensagem de carga distribuída enviada pelo VU nº ${__VU} - iteração ${__ITER}`,
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${data.token}`,
        },
    };

    const res = http.post(url, payload, params);

    if (res.status !== 201) {
        console.log(`Erro: ${res.status} - Body: ${res.body}`);
    }

    check(res, {
        'status é 201 (Created)': (r) => r.status === 201,
    });

    sleep(0.5); 
}