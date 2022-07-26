php console app:sentinel

To View Supervisr working go type:  tail -f /www/server/panel/plugin/supervisor/log/Sentinel.out.log

For some Reason.. if changed the name of the directory from 'crypto' to 'cryptobot'  it won't work.. I need to look into this and see why.


# Backlog & Todo List
- Create GUI Dashboard for CryptoBot
- Update the getData to use Binance Class File
- Test with other pairs; right now i'm only testing with USDT pairs.. It should be fine with stablecoin pairs.. like ADA/BUSD, ADA/USDC etc..  but for something like ADA/BTC hmm.. 

 
## Signal Bot 
- Add Additional Signals
- Upgrade the Stoch Signal so it doesn't keep buy / selling..
  - Stochastic sets a buy signal above the over sold line or above 50.. I need to adjust to not send a buy signal to the bot if there's a buy signal above the 50 line...
  - I need to make adjustment not to create a sell signal below the 40 line
  - any signals between the 40 and 50 will be considered the deadzone. 
  
## Database Structure 
- Add Bot Settings Table so we're not adding bunch of columns into the bot table. 


## Testing 
To test bots and indicators
Link: ```https://www.domain.com/path/signal/(BOTID)```


## Stochastic Indicator 
 - add a new item called ['strong'] for Sell and Buy. 
 - Test Max Trades.. It disabled the bot before it sold...  
 - I also want to add different items to the signal array .. because parobolic always stays above 80 buy and sell... This will be a problem ..it'll go up 60% without hitting strong buy
 - Sell (5m, 15m, 30m, 1h, 2h, 4h)
 - Buy  (5m, 15m, 30m, 1h, 2h, 4h)



# This falls under Bot Logic / AI 
Dynamic Stop Loss..  If Bot is up $300 i'm willing to lose 10% of that which is $30 .. so it'll 
create a stop loss of 3% or some won't lose more than $30 and it'll stop the bot.. This will
be able to determine that the bot was trading at the peak and road it til the end.

Maybe we can add a watcher.. If Bot is on hold watching if the trade drops more than 20% or hits support
and over sold... it'll jump back in..   The goal is to try not to get stuck at the top holding bags. 


ToDo
--------

- [ ] **Manager** - *Support multiple username connections on the same host in addConnectionFromConfig().*
- [ ] **Manager** - *Check alias name for \_slave to use replication slaves and separate the configs and connections in makeNewConnection().*
- [ ] **Manager** - *Set the default connection as master on instance creation.*
- [ ] **Connection** - *Log SQL failure and check if it is a MySQL Server has gone away error and needs to reconnect in query().*
- [ ] **Connection** - *Add the DB aliases for the connection and PDO connections attributes in \_\_toString().*
- [ ] **Manager** - *Incorporate the use of the env setting.*


