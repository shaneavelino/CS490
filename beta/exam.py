def o
	peration(op, a, b):
	if op == '+':
		return a + b
	elif op == '-':
		return a - b
	elif op == '*':
		return a * b
	elif op == '/':
		return a / b
	else:
		return -1 

string='"0", 2, 6'
number=u'"0", 2, 6'
if(string.isalpha()):
	operation(string)
if(number.isnumeric()):
	operation(int(number))