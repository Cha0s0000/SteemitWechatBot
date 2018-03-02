#encoding:UTF-8
import json, os, sys, time,socket
import requests
from contextlib import suppress
from concurrent import futures
from steem.blockchain import Blockchain
from steem.steemd import Steemd


env_dist = os.environ
api_url = env_dist.get('API_URL')
worker_num = env_dist.get('WORKER_NUM')
if worker_num == None:
    worker_num = 5
print('Worker num: %s' % (worker_num))
worker_num = int(worker_num)
env_block_num = env_dist.get('BLOCK_NUM')
start_block_num = 0

steemd_nodes = [
    'https://rpc.buildteam.io',
    'https://api.steemit.com',
]
s = Steemd(nodes=steemd_nodes)
b = Blockchain(s)

def worker(start, end):
    global s, b,send_to_workerman
    print('start from {start} to {end}'.format(start=start, end=end))
    block_infos = s.get_blocks(range(start, end+1))
    # print(block_infos)
    for block_info in block_infos:
        transactions = block_info['transactions']
        for trans in transactions:
            operations = trans['operations']
            for op in operations:
                postdata = json.dumps(op)
                send_to_workerman=socket.socket(socket.AF_INET,socket.SOCK_STREAM)
                send_to_workerman.connect(('192.168.2.1',8282)) 
                send_to_workerman.send(postdata.encode('utf-8'))
                send_to_workerman.close()
                    
def run():
    global start_block_num
    steemd_nodes = [
        'https://rpc.buildteam.io',
        'https://api.steemit.com',
    ]
    s = Steemd(nodes=steemd_nodes)
    b = Blockchain(s)
    
    

    while True:
        head_block_number = b.info()['head_block_number']
        end_block_num = int(head_block_number)
        if start_block_num == 0:
            start_block_num = end_block_num - 3
        if start_block_num >= end_block_num:
            continue
        with futures.ThreadPoolExecutor(max_workers=worker_num) as executor:
            executor.submit(worker, start_block_num, end_block_num)
        start_block_num = end_block_num + 1
        time.sleep(3)

if __name__ == '__main__':
    with suppress(KeyboardInterrupt):run()
    