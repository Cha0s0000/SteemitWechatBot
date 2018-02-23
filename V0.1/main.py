from queue import Queue
import random,threading,time
import json
from contextlib import suppress
from steem.blockchain import Blockchain
from steem.steemd import Steemd

from wxpy import *

#生产者类
class Producer(threading.Thread):
    def __init__(self, name,queue):
        threading.Thread.__init__(self, name=name)
        self.data=queue
 
    def run(self):
        steemd_nodes = [
            'https://api.steemit.com',
        ]
        s = Steemd(nodes=steemd_nodes)
        b = Blockchain(s)
        while True:
            head_block_number = b.info()['head_block_number']
            end_block_num = int(head_block_number)

            start_block_num = end_block_num - 1
            block_infos = s.get_blocks(range(start_block_num, end_block_num))
            print('start from {start} to {end}'.format(start=start_block_num, end=end_block_num))
            for block_info in block_infos:
                transactions = block_info['transactions']
                for trans in transactions:
                    operations = trans['operations']
                    self.data.put(operations)
            time.sleep(3)
            # print("%s finished!" % self.getName())
 
#消费者类
class Consumer(threading.Thread):
    def __init__(self,name,queue,MemberList,bot):
        threading.Thread.__init__(self,name=name)
        self.data=queue
        self.MemberList=MemberList
        self.bot=bot
    def run(self):
       
        while True:
            operations = self.data.get()
               
            if operations != None:
                for op in operations:
                    # print(op)
                    if op[0] == 'vote':
                        if op[1]['author'] in self.MemberList:
                            voter = op[1]['voter']
                            author = op[1]['author']
                            weight = op[1]['weight']
                            permlink = 'https://steemit.com/@'+author+'/'+op[1]['permlink']
                            postdata = json.dumps(op[1])
                            print(self.MemberList)
                            print("\n")
                            print(postdata)
                            InformFriend = ensure_one(self.bot.friends().search(author))
                            InformFriend.send("@{}\n--------\n你的文章有新点赞\n————————————\nvoter:{}\nweight:{}\npermlink:\n{} ".format(author,voter,weight,permlink))

                            # self.logger.warning("@{}\n--------\n你的文章有新点赞\n————————————\n voter:{}\n weight:{}\n permlink:{} ".format(author,voter,weight,permlink))
            # print("%s finished!" % self.getName())

class WechatMem(threading.Thread):
    def __init__(self,name,bot,MemberList):
        threading.Thread.__init__(self,name=name)
        self.bot=bot
        self.MemberList=MemberList
    def run(self):
        
        while True:
            SteemitBotWechatGroup = ensure_one(self.bot.groups().search('SteemitBot'))
            SteemitBotWechatGroup.update_group(True)
            for member in SteemitBotWechatGroup:
                friend = ensure_one(self.bot.friends().search(member.name))
            
                if (friend.name !=""):
                    # print(friend.remark_name)
                    if friend.name in self.MemberList:
                        continue
                    else:
                        self.MemberList.append(friend.name)
            print('update SteemitBot list')
            print(self.MemberList)
            time.sleep(10)
            # print(self.MemberList)


def main():
    bot = Bot()
    
    MemberList = []

    # logger = get_wechat_logger(group_receiver)
    # voting_list = ["angelina6688", "enjoyy","justyy"]
    queue = Queue()
    producer = Producer('Producer',queue)
    consumer = Consumer('Consumer',queue,MemberList,bot)
    consumer_1 = Consumer('Consumer',queue,MemberList,bot)
    consumer_2 = Consumer('Consumer',queue,MemberList,bot)
    GetWechatMem = WechatMem('WechatMem',bot,MemberList)

    producer.start()
    consumer.start()
    consumer_1.start()
    consumer_2.start()
    GetWechatMem.start()
 
    producer.join()
    consumer.join()
    consumer_1.join()
    consumer_2.join()
    GetWechatMem.join()

 
if __name__ == '__main__':
    main()