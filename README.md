# SteemitWechatBot 

------

## Introduction

> As all know ,**wechat** ia a popular communication tool in China with 8  hundred million users.Just like the **twitter** and **facebook**

**This is a tool that allows you to get a mention message through Wechat application when somebody in Steemit gives  a upvote to your post.**

------

## Why to develop this mention bot in wechat

Always , we do not sit  before the computer to view Steemit for all of our time.So it is necessary to have a mention tool which can remind us of the latest new in the Steemit wherever and whenever we are.

------

## How it works

The project uses a **multi thread** and the **Producer-Consumer** as the running framework.

There are 5 threads running at the same time

- one **WechatMem**
- one **Producer**
- three **Consumer**

Firstly,through the thread of **WechatMem**,get the member of the wechat group,who are the users will get the mention information of the Steemit upvote.

Secondly, while running the thread of  **Producer** to get the latest data the the Steemit block,three threads of **Consumer** read the **queue** to get the related data of the upvote.

When getting the data related to the member of the group ,the bot will send a direct message to the user in the Wechat application.



------

## How to use it 

### Requirement

- Python 3.6
- Steempython
- wxpy

### Setup

1. **Install Python 3.6**

2. **Install [Steempython](https://github.com/Netherdrake/steem-python)**

   For installing the Steempython lib , you had better use Ubuntu system instead of Windows system.

   ```
   pip install -U steem
   ```

3. **Install wxpy**

   ```
   pip install -U wxpy
   ```

4. **Get the code of this project**

   ```
   git pull https://github.com/Cha0s0000/SteemitWechatBot.git
   ```

5. **Edit the code**

   ```
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
   ```

   - In the main.py file line 76.Change it with your own wechat group name.

     ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519139750/tcrf8shsxd49y0bfo8d6.png)

   ```
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

   ```

   - In the main.py file line 52 and 53,Change the type of operation instead of **voting** leads to receive other type of mention information.

     ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519140238/cn5c4bwkeoamnqn1q146.png)

   - In the main.py line 63,change the template to inform user

     ![图片.png](https://res.cloudinary.com/hpiynhbhq/image/upload/v1519141797/nrh8fe8iynmhp6xaijqe.png)	

6. Run the code

   ```
   python main.py
   ```

   ​